<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseCategoryFactory> */
    use HasFactory;

    /**
     * Mass-assignable attributes (no custom columns beyond id and timestamps).
     */
    protected $fillable = [];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
