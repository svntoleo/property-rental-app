<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StayCategory extends Model
{
    /** @use HasFactory<\Database\Factories\StayCategoryFactory> */
    use HasFactory;

    /**
     * Mass-assignable attributes (no custom columns beyond id and timestamps).
     */
    protected $fillable = [];

    public function stays()
    {
        return $this->hasMany(Stay::class);
    }
}
