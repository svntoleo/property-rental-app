<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stay>
 */
class StayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
    // Factory default: short forward-looking stay (non-overlapping responsibility handled by seeder/business rules)
    $startDate = fake()->dateTimeBetween('-3 months', '+3 months');
    // Ensure end >= start and max 6 months span to keep realistic sizes
    $endDate = (clone $startDate)->modify('+' . rand(1, 180) . ' days');
        
        return [
            'accommodation_id' => \App\Models\Accommodation::factory(),
            'stay_category_id' => \App\Models\StayCategory::factory(),
            'price' => fake()->randomFloat(2, 500, 5000),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'due_date' => fake()->optional()->numberBetween(1, 28), // Day of month (1-28 to be safe for all months)
        ];
    }
}
