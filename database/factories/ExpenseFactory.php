<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => \App\Models\Property::factory(),
            'accommodation_id' => \App\Models\Accommodation::factory(),
            'expense_category_id' => \App\Models\ExpenseCategory::factory(),
            'label' => fake()->words(2, true),
            'price' => fake()->randomFloat(2, 50, 2000),
            'date' => fake()->dateTimeBetween('-1 year', 'now'),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
