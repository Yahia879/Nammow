<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::firstOrCreate(['name' => 'Basic Plan'], [
            'description' => 'Basic HR features',
            'price' => 29.99,
            'duration_days' => 30,
            'status' => 'active',
        ]);

        Plan::firstOrCreate(['name' => 'Professional Plan'], [
            'description' => 'HR + companies management',
            'price' => 99.99,
            'duration_days' => 30,
            'status' => 'active',
        ]);

        Plan::firstOrCreate(['name' => 'Enterprise Plan'], [
            'description' => 'Full SaaS features',
            'price' => 249.99,
            'duration_days' => 30,
            'status' => 'active',
        ]);
    }
}
