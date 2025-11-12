<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StayCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Short-term', 'Long-term', 'Monthly', 'Seasonal', 'Student', 'Corporate'];
        
        foreach ($categories as $category) {
            \App\Models\StayCategory::create(['label' => $category]);
        }
    }
}
