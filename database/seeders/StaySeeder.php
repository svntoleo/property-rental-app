<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stayCategories = \App\Models\StayCategory::all();
        
        // Create 1-3 stays for each accommodation
        \App\Models\Accommodation::all()->each(function ($accommodation) use ($stayCategories) {
            \App\Models\Stay::factory()
                ->count(rand(1, 3))
                ->create([
                    'accommodation_id' => $accommodation->id,
                    'stay_category_id' => $stayCategories->random()->id,
                ]);
        });
    }
}
