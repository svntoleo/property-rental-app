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
        
        // Create chronological, non-overlapping stays for each accommodation.
        // Logic: build a timeline from (now - 9 months) forward; each stay starts the day after previous ends.
        \App\Models\Accommodation::all()->each(function ($accommodation) use ($stayCategories) {
            $stayCount = rand(1, 4); // Slightly higher upper bound for variety
            $cursor = now()->subMonths(9)->startOfDay();

            for ($i = 0; $i < $stayCount; $i++) {
                // Random length between 1 and 6 months to reduce long overlaps
                $durationMonths = rand(1, 6);
                $startDate = $cursor->copy();
                $endDate = $startDate->copy()->addMonths($durationMonths)->subDay(); // inclusive end

                // Persist stay ensuring no overlap: since we advance cursor past endDate, earlier stays won't overlap
                \App\Models\Stay::create([
                    'accommodation_id' => $accommodation->id,
                    'stay_category_id' => $stayCategories->random()->id,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'price' => fake()->randomFloat(2, 800, 4000),
                    'due_date' => fake()->optional()->numberBetween(1, 28),
                ]);

                // Advance cursor to the next day after end plus a random gap (0–30 days)
                $gapDays = rand(0, 30);
                $cursor = $endDate->copy()->addDays(1 + $gapDays);
            }
        });
    }
}
