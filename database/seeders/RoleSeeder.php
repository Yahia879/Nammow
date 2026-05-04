<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Action;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin' => [
                'view_dashboard_super_admin',
                'view_clients', 'create_clients', 'edit_clients', 'delete_clients',
                'manage_settings',
                'view_companies', 'create_companies', 'edit_companies', 'delete_companies',
                'view_company_managers',
                'view_leaves', 'manage_leave_requests',
                'view_holidays', 'create_holidays', 'edit_holidays', 'delete_holidays',
                'manage_advances',
                'view_company_advances',
                'manage_advance_settings',
                'view_employee_salary',
            ],
            'client' => [
                'view_dashboard_client',
                'view_companies', 'create_companies', 'edit_companies', 'delete_companies',
                'view_company_managers',
                'view_leaves', 'manage_leave_requests',
                'view_holidays', 'create_holidays', 'edit_holidays', 'delete_holidays',
                'view_client_advances',
                'view_company_advances',
                'manage_advance_settings',
                'view_employee_salary',
            ],
            'company' => [
                'view_dashboard_company',
                'view_employees', 'create_employees', 'edit_employees', 'delete_employees',
                'view_attendance', 'import_attendance', 'manage_legacy_leaves',
                'view_fingerprints', 'import_fingerprints',
                'view_leaves', 'manage_leave_requests',
                'view_holidays', 'create_holidays', 'edit_holidays', 'delete_holidays',
                'view_structure', 'manage_structure',
                'view_messages', 'send_messages',
                'view_discounts', 'manage_discounts',
                'view_statistics',
                'view_assets', 'manage_assets',
                'view_asset_categories', 'manage_asset_categories',
                'view_asset_reports',
                'approve_advance', 'reject_advance', 'view_company_advances',
                'manage_advance_settings',
                'manage_employee_salary', 'view_employee_salary',
            ],
            'employee' => [
                'view_dashboard_employee',
                'view_my_leaves', 'create_leave_requests', 'delete_leave_requests',
                'view_my_holidays',
                'request_advance', 'view_my_advances',
                'view_my_salary',
            ],
        ];

        foreach ($roles as $roleName => $actions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $actionIds = Action::whereIn('name', $actions)->pluck('id')->toArray();
            $role->actions()->sync($actionIds);
        }
    }
}
