<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accommodation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Central list of allowed sort keys (virtual + real) for controllers/UI.
     */
    public const ALLOWED_SORTS = ['label', 'property', 'status', 'tenants', 'category', 'created_at'];

    /**
     * Automatically append computed occupancy attributes when serialized.
     */
    protected $appends = [
        'is_occupied',
        'active_stay_tenants',
        'active_stay_category',
        'active_stay_end_date',
    ];

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        'property_id',
        'label',
    ];

    public function property()
    {
        // Include soft-deleted properties for historical viewing
        return $this->belongsTo(Property::class)->withTrashed();
    }

    public function stays()
    {
        return $this->hasMany(Stay::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Relationship: the single active stay (if any) right now.
     */
    public function activeStay()
    {
        return $this->hasOne(Stay::class)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Scope: apply simple search against accommodation label and property label.
     */
    public function scopeSearch($query, ?string $term): void
    {
        if (empty($term)) {
            return;
        }
        $query->where(function ($q) use ($term) {
            $q->where('accommodations.label', 'like', "%{$term}%")
              ->orWhereHas('property', function ($p) use ($term) {
                  $p->where('properties.label', 'like', "%{$term}%");
              });
        });
    }

    /**
     * Scope: apply sorting for supported virtual columns (status, tenants, category, property) or direct columns.
     * Columns:
     *  - label (direct)
     *  - property (join properties)
     *  - status (active stay occupancy)
     *  - tenants (active stay tenant count)
     *  - category (active stay category label)
     */
    public function scopeWithActiveStayMeta($query): void
    {
        // Idempotent-ish: only apply if not already joined via alias
        // (simple heuristic: look for occupancy_flag select). If already present, skip.
        $sql = (string) $query->toBase()->toSql();
        if (str_contains($sql, 'occupancy_flag')) {
            return; // already applied
        }

        $query->leftJoin('stays as active_stay', function ($join) {
                $join->on('active_stay.accommodation_id', '=', 'accommodations.id')
                     ->where('active_stay.start_date', '<=', now())
                     ->where('active_stay.end_date', '>=', now())
                     ->whereNull('active_stay.deleted_at');
            })
            ->leftJoin('tenants', 'tenants.stay_id', '=', 'active_stay.id')
            ->leftJoin('stay_categories', 'stay_categories.id', '=', 'active_stay.stay_category_id')
            ->select('accommodations.*')
            ->selectRaw('CASE WHEN active_stay.id IS NOT NULL THEN 1 ELSE 0 END as occupancy_flag')
            ->selectRaw('COUNT(DISTINCT tenants.id) as active_tenants_count')
            ->selectRaw('COALESCE(stay_categories.label, "") as active_category_label')
            ->groupBy('accommodations.id', 'active_stay.id', 'stay_categories.label');
    }

    public function scopeSortBy($query, string $column, string $direction = 'asc'): void
    {
        $dir = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        switch ($column) {
            case 'property':
                $query->leftJoin('properties', 'properties.id', '=', 'accommodations.property_id')
                      ->select('accommodations.*')
                      ->orderBy('properties.label', $dir);
                break;
            case 'status':
            case 'tenants':
            case 'category':
                $query->withActiveStayMeta();
                if ($column === 'status') {
                    $query->orderBy('occupancy_flag', $dir);
                } elseif ($column === 'tenants') {
                    $query->orderBy('active_tenants_count', $dir);
                } else { // category
                    $query->orderBy('active_category_label', $dir);
                }
                break;
            default:
                $query->orderBy($column, $dir);
        }
    }

    /** @return array<int,string> */
    public static function allowedSorts(): array
    {
        return self::ALLOWED_SORTS;
    }

    /**
     * Accessor: whether the accommodation is currently occupied.
     */
    public function getIsOccupiedAttribute(): bool
    {
        if ($this->relationLoaded('activeStay')) {
            return (bool) $this->activeStay;
        }
        if ($this->relationLoaded('stays')) {
            return $this->stays->contains(function ($stay) {
                return $stay->start_date <= now() && $stay->end_date >= now();
            });
        }
        return false;
    }

    /**
     * Accessor: number of tenants in current active stay.
     */
    public function getActiveStayTenantsAttribute(): int
    {
        if (!$this->is_occupied) {
            return 0;
        }
        if ($this->relationLoaded('activeStay')) {
            $stay = $this->activeStay;
            if (!$stay) return 0;
            return $stay->tenants_count ?? ($stay->relationLoaded('tenants') ? $stay->tenants->count() : 0);
        }
        if ($this->relationLoaded('stays')) {
            $stay = $this->stays->first(function ($stay) {
                return $stay->start_date <= now() && $stay->end_date >= now();
            });
            if (!$stay) return 0;
            return $stay->tenants_count ?? ($stay->relationLoaded('tenants') ? $stay->tenants->count() : 0);
        }
        return 0;
    }

    /**
     * Accessor: label of active stay category, if any.
     */
    public function getActiveStayCategoryAttribute(): ?string
    {
        // If relation directly loaded
        if ($this->relationLoaded('activeStay')) {
            return $this->activeStay?->category?->label;
        }
        // If stays collection loaded
        if ($this->relationLoaded('stays')) {
            $stay = $this->stays->first(function ($stay) {
                return $stay->start_date <= now() && $stay->end_date >= now();
            });
            return $stay?->category?->label;
        }
        // If joined via meta scope (alias column present)
        if (array_key_exists('active_category_label', $this->attributes)) {
            $label = $this->attributes['active_category_label'];
            return $label !== '' ? $label : null;
        }
        return null;
    }

    /**
     * Accessor: end date of the active stay.
     */
    public function getActiveStayEndDateAttribute(): ?string
    {
        if ($this->relationLoaded('activeStay')) {
            return $this->activeStay?->end_date?->toDateString();
        }
        if ($this->relationLoaded('stays')) {
            $stay = $this->stays->first(function ($stay) {
                return $stay->start_date <= now() && $stay->end_date >= now();
            });
            return $stay?->end_date?->toDateString();
        }
        // When using meta scope we don't currently select end_date; could be extended later.
        return null;
    }
}
