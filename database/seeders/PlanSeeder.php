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
        Schema::disableForeignKeyConstraints();
        Plan::truncate();
        Schema::enableForeignKeyConstraints();

        Plan::create([
            'name' => 'خطة النظام',
            'description' => 'خطة النظام',
            'price' => 0,
            'duration_days' => 365,
            'status' => 'active',
        ]);

        Plan::create([
            'name' => 'خطة الموارد البشرية',
            'description' => 'خطة الموارد البشرية',
            'price' => 0,
            'duration_days' => 365,
            'status' => 'active',
        ]);
    }
}
