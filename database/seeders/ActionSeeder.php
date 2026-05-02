<?php

namespace Database\Seeders;

use App\Models\Action;
use Illuminate\Database\Seeder;

class ActionSeeder extends Seeder
{
    public function run(): void
    {
        $actions = [
            // SaaS & Administration
            ['name' => 'view_dashboard_super_admin', 'group' => 'dashboard'],
            ['name' => 'view_dashboard_client', 'group' => 'dashboard'],
            ['name' => 'view_clients', 'group' => 'clients'],
            ['name' => 'create_clients', 'group' => 'clients'],
            ['name' => 'edit_clients', 'group' => 'clients'],
            ['name' => 'delete_clients', 'group' => 'clients'],
            ['name' => 'manage_settings', 'group' => 'settings'],

            // Company Management
            ['name' => 'view_dashboard_company', 'group' => 'dashboard'],
            ['name' => 'view_companies', 'group' => 'companies'],
            ['name' => 'create_companies', 'group' => 'companies'],
            ['name' => 'edit_companies', 'group' => 'companies'],
            ['name' => 'delete_companies', 'group' => 'companies'],
            
            // HR (Company Level)
            ['name' => 'view_employees', 'group' => 'employees'],
            ['name' => 'create_employees', 'group' => 'employees'],
            ['name' => 'edit_employees', 'group' => 'employees'],
            ['name' => 'delete_employees', 'group' => 'employees'],
            
            ['name' => 'view_attendance', 'group' => 'attendance'],
            ['name' => 'import_attendance', 'group' => 'attendance'],
            ['name' => 'manage_legacy_leaves', 'group' => 'attendance'],
            ['name' => 'view_fingerprints', 'group' => 'attendance'],
            ['name' => 'import_fingerprints', 'group' => 'attendance'],
            
            ['name' => 'view_leaves', 'group' => 'leaves'],
            ['name' => 'manage_leave_requests', 'group' => 'leaves'],
            
            ['name' => 'view_holidays', 'group' => 'holidays'],
            ['name' => 'create_holidays', 'group' => 'holidays'],
            ['name' => 'edit_holidays', 'group' => 'holidays'],
            ['name' => 'delete_holidays', 'group' => 'holidays'],
            
            ['name' => 'view_structure', 'group' => 'structure'],
            ['name' => 'manage_structure', 'group' => 'structure'],
            
            ['name' => 'view_messages', 'group' => 'messages'],
            ['name' => 'send_messages', 'group' => 'messages'],
            
            ['name' => 'view_discounts', 'group' => 'discounts'],
            ['name' => 'manage_discounts', 'group' => 'discounts'],
            
            ['name' => 'view_statistics', 'group' => 'statistics'],

            // Employee Self-Service
            ['name' => 'view_dashboard_employee', 'group' => 'dashboard'],
            ['name' => 'view_my_leaves', 'group' => 'my_leaves'],
            ['name' => 'create_leave_requests', 'group' => 'my_leaves'],
            ['name' => 'delete_leave_requests', 'group' => 'my_leaves'],
            ['name' => 'view_my_holidays', 'group' => 'my_holidays'],

            // Assets Management
            ['name' => 'view_assets', 'group' => 'assets'],
            ['name' => 'manage_assets', 'group' => 'assets'],
            ['name' => 'view_asset_categories', 'group' => 'assets'],
            ['name' => 'manage_asset_categories', 'group' => 'assets'],
            ['name' => 'view_asset_reports', 'group' => 'assets'],
        ];

        foreach ($actions as $action) {
            Action::firstOrCreate(['name' => $action['name']], $action);
        }
    }
}
