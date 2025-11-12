<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    use HasFactory;

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

    /**
     * Attributes to append to the model's array / JSON form.
     */
    protected $appends = [
        'cpf_formatted',
        'phone_formatted',
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    /**
     * Mutator: normalize CPF to digits-only before saving.
     */
    public function setCpfAttribute($value)
    {
        $normalized = $value ? preg_replace('/\D/', '', (string) $value) : null;
        $this->attributes['cpf'] = $normalized === '' ? null : $normalized;
    }

    /**
     * Mutator: normalize phone to digits-only before saving.
     */
    public function setPhoneAttribute($value)
    {
        $normalized = $value ? preg_replace('/\D/', '', (string) $value) : null;
        $this->attributes['phone'] = $normalized === '' ? null : $normalized;
    }

    /**
     * Accessor: formatted CPF (000.000.000-00) or null.
     */
    public function getCpfFormattedAttribute()
    {
        $cpf = $this->attributes['cpf'] ?? null;
        if (empty($cpf) || strlen($cpf) !== 11) {
            return null;
        }
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }

    /**
     * Accessor: formatted phone number for Brazilian formats.
     * Examples:
     * - 11912345678 -> (11) 91234-5678
     * - 1123456789  -> (11) 2345-6789
     * - 912345678   -> 91234-5678
     */
    public function getPhoneFormattedAttribute()
    {
        $phone = $this->attributes['phone'] ?? null;
        if (empty($phone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        // Remove leading country code 55 if present
        if (strlen($digits) > 11 && substr($digits, 0, 2) === '55') {
            $digits = substr($digits, 2);
        }

        $len = strlen($digits);

        if ($len === 11) {
            // (AA) 9XXXX-XXXX
            return '(' . substr($digits, 0, 2) . ') ' . substr($digits, 2, 5) . '-' . substr($digits, 7, 4);
        }

        if ($len === 10) {
            // (AA) XXXX-XXXX
            return '(' . substr($digits, 0, 2) . ') ' . substr($digits, 2, 4) . '-' . substr($digits, 6, 4);
        }

        if ($len === 9) {
            // 9XXXX-XXXX (no area code)
            return substr($digits, 0, 5) . '-' . substr($digits, 5, 4);
        }

        if ($len === 8) {
            // XXXX-XXXX
            return substr($digits, 0, 4) . '-' . substr($digits, 4, 4);
        }

        // Fallback: return digits as-is
        return $digits;
    }
}
