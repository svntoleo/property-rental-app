<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    /** @use HasFactory<\Database\Factories\AccommodationFactory> */
    use HasFactory;

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function stays()
    {
        return $this->hasMany(Stay::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
