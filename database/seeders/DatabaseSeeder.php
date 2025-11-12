<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed users
        $this->call(UserSeeder::class);

        // Seed categories first (they have no dependencies)
        $this->call([
            StayCategorySeeder::class,
            ExpenseCategorySeeder::class,
        ]);

        // Seed properties
        $this->call(PropertySeeder::class);

        // Seed accommodations (depends on properties)
        $this->call(AccommodationSeeder::class);

        // Seed stays (depends on accommodations and stay categories)
        $this->call(StaySeeder::class);

        // Seed tenants (depends on stays)
        $this->call(TenantSeeder::class);

        // Seed expenses (depends on properties, accommodations, and expense categories)
        $this->call(ExpenseSeeder::class);
    }
}
