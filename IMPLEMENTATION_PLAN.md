# Dynamic Action/Permission System Implementation Plan

## 1. Modules & Extracted Actions
Based on a scan of `routes/web.php` and `verticalMenu.json`, here are the actions required for the system:

### SaaS & Administration
- `view_dashboard_super_admin`
- `view_dashboard_client`
- `view_clients`, `create_clients`, `edit_clients`, `delete_clients`
- `manage_settings` (roles, users, logs)

### Company Management
- `view_dashboard_company`
- `view_companies`, `create_companies`, `edit_companies`, `delete_companies`

### Human Resources (Company Manager Level)
- `view_employees`, `create_employees`, `edit_employees`, `delete_employees`
- `view_attendance`, `import_attendance`, `manage_legacy_leaves`
- `view_fingerprints`, `import_fingerprints`
- `view_leaves`, `manage_leave_requests` (approve/reject)
- `view_holidays`, `create_holidays`, `edit_holidays`, `delete_holidays`
- `view_structure` (centers, departments, positions), `manage_structure`
- `view_messages`, `send_messages` (bulk & personal)
- `view_discounts`, `manage_discounts`
- `view_statistics`

### Employee Self-Service
- `view_dashboard_employee`
- `view_my_leaves`, `create_leave_requests`, `delete_leave_requests`
- `view_my_holidays`

### Assets Management
- `view_assets`, `manage_assets`
- `view_asset_categories`, `manage_asset_categories`
- `view_asset_reports`

---

## 2. Database Structure (Migrations)
We will completely replace the current `Spatie\Permission` package structure to meet your custom requirements.
1. **Drop Spatie Tables:** Create a migration to drop `model_has_permissions`, `model_has_roles`, `role_has_permissions`, `permissions`, and `roles`.
2. **Create New Tables:**
   - `roles` (id, name, description, timestamps)
   - `actions` (id, name, group, timestamps)
   - `action_role` (action_id, role_id)
   - `action_user` (action_id, user_id)
3. **Update Users Table:** Add `role_id` (foreign key referencing `roles.id`).

---

## 3. Models & Relationships
- **Role:** `hasMany` Users, `belongsToMany` Actions.
- **Action:** `belongsToMany` Roles, `belongsToMany` Users.
- **User:**
  - Remove Spatie's `HasRoles` trait.
  - Add `belongsTo` Role.
  - Add `belongsToMany` Actions.
  - Implement `canAction(string|array $action)`:
    ```php
    public function canAction($actionName) {
        $actions = is_array($actionName) ? $actionName : [$actionName];
        
        // Check direct user actions
        if ($this->actions()->whereIn('name', $actions)->exists()) return true;
        
        // Check role actions
        if ($this->role && $this->role->actions()->whereIn('name', $actions)->exists()) return true;
        
        return false;
    }
    ```

---

## 4. Middleware Setup
- Create `CheckAction` middleware (`check.action:action_name`).
- It will verify `$request->user()->canAction($action_name)`. If false, return 403.
- Register in `app/Http/Kernel.php`.

---

## 5. Route Updates
- Refactor `routes/web.php` to replace the old `role:xxx` and `role_redirect:xxx` middleware with `check.action:xxx` middleware based on the extracted actions.

---

## 6. Seeders
- Create `ActionSeeder` to insert all extracted actions into the database.
- Create `RoleSeeder` to create default roles (`super_admin`, `client`, `company`, `employee`) and attach the corresponding actions.
- Update `DatabaseSeeder`, `TenantSeeder`, and `TestEmployeeSeeder` to assign users via `role_id` instead of `$user->assignRole()`.

---

## 7. UI & UI Elements
- Update `resources/menu/verticalMenu.json` logic in the sidebar rendering to check `auth()->user()->canAction(...)` instead of `hasRole`.
- Update Blade views to conditionally render CRUD buttons based on `canAction`.

---

## Next Steps
I will exit plan mode and begin the implementation step-by-step carefully ensuring that no existing logic breaks.