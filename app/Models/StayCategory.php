<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StayCategory extends Model
{
    /** @use HasFactory<\Database\Factories\StayCategoryFactory> */
    use HasFactory;

    public function stays()
    {
        return $this->hasMany(Stay::class);
    }
}
