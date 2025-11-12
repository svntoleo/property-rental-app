<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseCategoryFactory> */
    use HasFactory;

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        'label',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
