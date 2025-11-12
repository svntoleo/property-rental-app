<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stay extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        'accommodation_id',
        'stay_category_id',
        'start_date',
        'end_date',
        'due_date',
        'price'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'due_date' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function accommodation()
    {
        // Include soft-deleted accommodations so history remains viewable
        return $this->belongsTo(Accommodation::class)->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(StayCategory::class, 'stay_category_id');
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * Get the number of days for this stay.
     */
    public function getDaysAttribute(): int
    {
        return Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date));
    }

    /**
     * Get the price per day.
     */
    public function getPricePerDayAttribute(): float
    {
        $days = $this->days;

        return $days > 0 ? $this->price / $days : 0;
    }

    /**
     * Scope a query to only include active stays.
     */
    public function scopeActive($query)
    {
        return $query->where('end_date', '>=', now());
    }
}
