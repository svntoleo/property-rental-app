<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseFactory> */
    use HasFactory;

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class);
    }
}
