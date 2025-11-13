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
     * Format phone with mask: 11987654321 -> (11) 98765-4321
     */
    public function getPhoneFormattedAttribute(): string
    {
        if (! $this->phone) {
            return '';
        }

        // Remove any non-digit characters first
        $phone = preg_replace('/\D/', '', $this->phone);

        // Apply mask based on length
        // Mobile: (XX) 9XXXX-XXXX (11 digits)
        // Landline: (XX) XXXX-XXXX (10 digits)
        if (strlen($phone) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone);
        } elseif (strlen($phone) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone);
        }

        return $phone; // Return as-is if unexpected format
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
