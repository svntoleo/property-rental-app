<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expenseCategories = \App\Models\ExpenseCategory::all();
        
        // Create 3-7 expenses for each property
        \App\Models\Property::all()->each(function ($property) use ($expenseCategories) {
            $accommodations = $property->accommodations;
            
            \App\Models\Expense::factory()
                ->count(rand(3, 7))
                ->create([
                    'property_id' => $property->id,
                    'accommodation_id' => $accommodations->random()->id,
                    'expense_category_id' => $expenseCategories->random()->id,
                ]);
        });
    }
}
