<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Utilities', 'Maintenance', 'Insurance', 'Taxes', 'Repairs', 'Cleaning', 'HOA Fees'];
        
        foreach ($categories as $category) {
            \App\Models\ExpenseCategory::create(['label' => $category]);
        }
    }
}
