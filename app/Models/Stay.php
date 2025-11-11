<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Stay extends Model
{
    /** @use HasFactory<\Database\Factories\StayFactory> */
    use HasFactory;

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        'accommodation_id',
        'stay_category_id',
        'price',
        'start_date',
        'end_date',
    ];

    /**
     * Cast date fields to Carbon instances and price to decimal.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
    ];

    /**
     * Append the computed `active` attribute when serializing.
     */
    protected $appends = ['active'];

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function category()
    {
        return $this->belongsTo(StayCategory::class);
    }

    public function tenants() {
        return $this->hasMany(Tenant::class);
    }

    /**
     * Computed attribute: whether the stay is active today.
     * Returns boolean.
     */
    public function getActiveAttribute(): bool
    {
        if (is_null($this->start_date) || is_null($this->end_date)) {
            return false;
        }

        $today = Carbon::today();

        // Carbon::between accepts two DateTimes and is inclusive by default when third arg true
        return $today->between($this->start_date, $this->end_date, true);
    }

    /**
     * Scope to filter only active stays.
     * Use: Stay::active()->get();
     */
    public function scopeActive($query)
    {
        // Use application UTC date for consistent, testable comparisons.
        $today = now()->utc()->toDateString();

        return $query->whereDate('start_date', '<=', $today)
                     ->whereDate('end_date', '>=', $today);
    }
}
