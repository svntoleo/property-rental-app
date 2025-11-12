<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accommodation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        'property_id',
        'label',
    ];

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
