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
        
        // Create 1-3 non-overlapping stays for each accommodation
        \App\Models\Accommodation::all()->each(function ($accommodation) use ($stayCategories) {
            $stayCount = rand(1, 3);
            $lastEndDate = now()->subYear(); // Start from a year ago
            
            for ($i = 0; $i < $stayCount; $i++) {
                // Start the next stay at least 1 day after the previous one ended
                $gapDays = rand(1, 90); // 1-90 days gap between stays
                $startDate = \Carbon\Carbon::parse($lastEndDate)->addDays($gapDays);
                
                // End date is 1-12 months after start date
                $durationMonths = rand(1, 12);
                $endDate = $startDate->copy()->addMonths($durationMonths);
                
                \App\Models\Stay::factory()->create([
                    'accommodation_id' => $accommodation->id,
                    'stay_category_id' => $stayCategories->random()->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);
                
                $lastEndDate = $endDate;
            }
        });
    }
}
