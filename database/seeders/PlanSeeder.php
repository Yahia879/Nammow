<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // To strictly follow the requirement of "Delete all current records from the plans table"
        // we can truncate, but to make it idempotent and safe for existing subscriptions, 
        // we should ideally keep the IDs or handle them. 
        // However, the user explicitly asked to "Delete all current records".
        
        Schema::disableForeignKeyConstraints();
        Plan::truncate();
        Schema::enableForeignKeyConstraints();

        $plans = [
            [
                'name' => 'خطة النظام',
                'description' => 'خطة النظام',
                'price' => 0,
                'duration_days' => 365,
                'status' => 'active',
            ],
            [
                'name' => 'خطة الموارد البشرية',
                'description' => 'خطة الموارد البشرية',
                'price' => 0,
                'duration_days' => 365,
                'status' => 'active',
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
