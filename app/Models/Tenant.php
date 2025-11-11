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
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }
}
