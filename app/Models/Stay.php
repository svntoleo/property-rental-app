<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stay extends Model
{
    /** @use HasFactory<\Database\Factories\StayFactory> */
    use HasFactory;

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
}
