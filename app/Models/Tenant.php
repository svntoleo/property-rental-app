<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        'stay_id',
        'name',
        'email',
        'phone',
        'cpf',
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    /**
     * Get the tenant's masked CPF.
     */
    public function getMaskedCpfAttribute(): string
    {
        if (! $this->cpf) {
            return '';
        }

        // Mask CPF: 123.456.789-10 -> 123.***.**-10
        return preg_replace('/(\d{3})\.\d{3}\.\d{2}(-\d{2})/', '$1.***.**$2', $this->cpf);
    }

    /**
     * Format CPF with mask: 12345678910 -> 123.456.789-10
     */
    public function getFormattedCpfAttribute(): string
    {
        if (! $this->cpf) {
            return '';
        }

        // Remove any non-digit characters first
        $cpf = preg_replace('/\D/', '', $this->cpf);

        // Apply mask
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    /**
     * Set CPF attribute, removing any formatting.
     */
    public function setCpfAttribute($value): void
    {
        // Store CPF without formatting
        $this->attributes['cpf'] = preg_replace('/\D/', '', $value);
    }

    /**
     * Scope a query to search by name, email, or CPF.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('cpf', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to only include tenants with active stays.
     */
    public function scopeWithActiveStays($query)
    {
        return $query->whereHas('stay', function ($q) {
            $q->where('end_date', '>=', now());
        });
    }

    /**
     * Get tenant's full accommodation information through stay.
     */
    public function getAccommodationAttribute()
    {
        return $this->stay?->accommodation;
    }

    /**
     * Get tenant's property information through stay and accommodation.
     */
    public function getPropertyAttribute()
    {
        return $this->stay?->accommodation?->property;
    }

    /**
     * Check if tenant's stay is currently active.
     */
    public function getIsActiveAttribute(): bool
    {
        if (! $this->stay) {
            return false;
        }

        return $this->stay->end_date >= now();
    }
}
