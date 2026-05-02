<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Annual Leave', 'description' => 'Regular yearly vacation'],
            ['name' => 'Sick Leave', 'description' => 'Leave for medical reasons'],
            ['name' => 'Unpaid Leave', 'description' => 'Leave without pay'],
            ['name' => 'Maternity/Paternity Leave', 'description' => 'Leave for new parents'],
        ];

        foreach ($types as $type) {
            LeaveType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
