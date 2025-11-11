<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    /** @use HasFactory<\Database\Factories\PropertyFactory> */
    use HasFactory;

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        'label',
        'address',
    ];

    public function accommodations()
    {
        return $this->hasMany(Accommodation::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
