<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ActionSeeder::class,
            RoleSeeder::class,
            PlanSeeder::class,
            ContractsSeeder::class,
            EmployeesSeeder::class,
            AdminUserSeeder::class,
            CenterSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            TimelineSeeder::class,
            TenantSeeder::class,
            LeaveTypeSeeder::class,
            TestEmployeeSeeder::class,
        ]);

        if (file_exists('database/seeders/SettingsSeeder.php')) {
            $this->call([
                SettingsSeeder::class,
            ]);
        }

        // Assign super_admin role to the first user
        $admin = User::find(1);
        if ($admin) {
            $superAdminRole = \App\Models\Role::where('name', 'super_admin')->first();
            if ($superAdminRole) {
                $admin->update(['role_id' => $superAdminRole->id]);
            }
        }
    }
}
