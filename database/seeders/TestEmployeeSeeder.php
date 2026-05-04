<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $client = Client::where('slug', 'nammow')->first();
        $company = Company::where('client_id', $client->id)->first();

        // 1. Create the Employee record
        $employee = Employee::updateOrCreate(['national_number' => '12345678'], [
            'company_id' => $company->id,
            'contract_id' => 1,
            'join_date' => now()->startOfYear()->format('Y-m-d'),
            'first_name' => 'John',
            'father_name' => 'Doe',
            'last_name' => 'Employee',
            'mother_name' => 'Jane',
            'birth_and_place' => 'City',
            'mobile_number' => '0912345678',
            'degree' => 'Bachelor',
            'gender' => 1,
            'address' => 'Test Address',
            'profile_photo_path' => 'profile-photos/.default-photo.jpg',
            'created_by' => 'System',
            'updated_by' => 'System',
            'is_active' => true,
            'annual_leave_days' => 21,
        ]);

        // 2. Create the User account
        $user = User::firstOrCreate(['email' => 'employee@nammow.com'], [
            'name' => 'John Doe',
            'username' => 'john_doe',
            'password' => bcrypt('password'),
            'client_id' => $client->id,
            'company_id' => $company->id,
            'employee_id' => $employee->id,
        ]);

        $employeeRole = \App\Models\Role::where('name', 'employee')->first();
        if ($employeeRole) {
            $user->update(['role_id' => $employeeRole->id]);
        }

        // 3. Create Timeline
        \App\Models\Timeline::updateOrCreate(['employee_id' => $employee->id], [
            'center_id' => 1,
            'department_id' => 1,
            'position_id' => 1,
            'start_date' => now()->startOfYear()->format('Y-m-d'),
            'created_by' => 'System',
            'updated_by' => 'System',
        ]);
    }
}
