<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\Plan;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Ensure contracts exist
        if (\App\Models\Contract::count() === 0) {
            $this->call(ContractsSeeder::class);
        }

        // 1. Ensure a plan exists
        $plan = Plan::firstOrCreate(['name' => 'Enterprise Plan'], [
            'description' => 'Full SaaS features',
            'price' => 249.99,
            'duration_days' => 30,
            'status' => 'active',
        ]);

        // 2. Create a Client (Tenant)
        $client = Client::firstOrCreate(['slug' => 'nammow'], [
            'name' => 'Nammow Client',
            'email' => 'client@nammow.com',
            'phone' => '123456789',
            'status' => 'active',
            'plan_id' => $plan->id,
            'is_active' => true,
        ]);

        // 3. Create a Company for the Client
        $company = Company::firstOrCreate(['client_id' => $client->id, 'name' => 'Nammow HR'], [
            'address' => 'Main Street, City',
            'status' => 'active',
        ]);

        // 4. Create an Employee for the Client Admin
        $employee = Employee::firstOrCreate(['national_number' => '00000000'], [
            'company_id' => $company->id,
            'first_name' => 'Client',
            'father_name' => 'System',
            'last_name' => 'Admin',
            'mother_name' => 'System',
            'birth_and_place' => 'System',
            'degree' => 'System',
            'contract_id' => 1, // Assuming a contract exists
            'mobile_number' => '0900000000',
            'gender' => 1,
            'address' => 'Client Address',
            'profile_photo_path' => 'profile-photos/.default-photo.jpg',
            'created_by' => 'System',
            'updated_by' => 'System',
            'is_active' => true,
        ]);

        // 5. Create a Client Admin User
        $clientAdmin = User::firstOrCreate(['email' => 'client@nammow.com'], [
            'name' => 'Client Admin',
            'username' => 'client_admin',
            'password' => bcrypt('password'),
            'client_id' => $client->id,
            'company_id' => $company->id,
            'employee_id' => $employee->id,
        ]);

        $clientAdmin->assignRole('client');

        // 6. Add to Company Managers
        \Illuminate\Support\Facades\DB::table('company_managers')->updateOrInsert(
            ['company_id' => $company->id, 'user_id' => $clientAdmin->id],
            ['role' => 'admin', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]
        );

        // 7. Ensure Super Admin exists and is updated
        $superAdmin = User::firstOrCreate(['email' => 'admin@nammow.com'], [
            'name' => 'Super Admin',
            'username' => 'super_admin',
            'password' => bcrypt('password'),
        ]);

        $superAdmin->assignRole('super_admin');
    }
}
