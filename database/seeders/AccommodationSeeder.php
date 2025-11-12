<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccommodationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 2-5 accommodations for each property
        \App\Models\Property::all()->each(function ($property) {
            \App\Models\Accommodation::factory()
                ->count(rand(2, 5))
                ->create(['property_id' => $property->id]);
        });
    }
}
