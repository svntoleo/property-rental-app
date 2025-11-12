<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        'label',
        'address',
        'description',
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
