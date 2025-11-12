<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 1-2 tenants for each stay
        \App\Models\Stay::all()->each(function ($stay) {
            \App\Models\Tenant::factory()
                ->count(rand(1, 2))
                ->create(['stay_id' => $stay->id]);
        });
    }
}
