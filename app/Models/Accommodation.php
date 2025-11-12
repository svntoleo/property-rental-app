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
        // Include soft-deleted properties for historical viewing
        return $this->belongsTo(Property::class)->withTrashed();
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
