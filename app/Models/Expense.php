<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        'property_id',
        'accommodation_id',
        'expense_category_id',
        'label',                                                                                                                                                                                                                                                                                    
        'price',
        'date',
        'description',
    ];                                                                          

    /**
     * Cast price to decimal.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Auto-append helpful computed attributes.
     */
    protected $appends = [
        'is_current_month',
    ];

    /**
     * Allowed sorts list (controllers should reference for validation).
     */
    public const ALLOWED_SORTS = ['label', 'price', 'date', 'description', 'current_month'];

    public static function allowedSorts(): array
    {
        return self::ALLOWED_SORTS;
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * Accessor: whether the expense date is within the current month.
     */
    public function getIsCurrentMonthAttribute(): bool
    {
        if (!$this->date) return false;
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        return $this->date->between($monthStart, $monthEnd);
    }

    /**
     * Scope: restrict expenses to current month.
     */
    public function scopeCurrentMonth($query): void
    {
        $query->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    /**
     * Scope: apply sorting based on allowed sort keys (including virtual current_month).
     */
    public function scopeSortBy($query, string $column, string $direction = 'asc'): void
    {
        $dir = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        switch ($column) {
            case 'current_month':
                $start = now()->startOfMonth()->toDateString();
                $end = now()->endOfMonth()->toDateString();
                $query->orderByRaw("CASE WHEN date BETWEEN '$start' AND '$end' THEN 1 ELSE 0 END $dir");
                break;
            case 'label':
            case 'price':
            case 'date':
            case 'description':
                $query->orderBy($column, $dir);
                break;
            default:
                // fallback to date for unknown columns
                $query->orderBy('date', $dir);
        }
    }

    /**
     * Scope: search expenses by label, description, or category label.
     */
    public function scopeSearch($query, ?string $term): void
    {
        if (empty($term)) return;
        $query->where(function ($q) use ($term) {
            $q->where('label', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhereHas('category', function ($cat) use ($term) {
                  $cat->where('label', 'like', "%{$term}%");
              });
        });
    }
}
