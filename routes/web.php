<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\language\LanguageController;
use App\Livewire\Assets\Categories;
use App\Livewire\Assets\Inventory;
use App\Livewire\ContactUs;
use App\Livewire\Dashboard;
use App\Livewire\HumanResource\Attendance\Fingerprints;
use App\Livewire\HumanResource\Attendance\Leaves;
use App\Livewire\HumanResource\Discounts;
use App\Livewire\HumanResource\Holidays\EmployeeHolidays;
use App\Livewire\HumanResource\Holidays\HolidayManagement;
use App\Livewire\HumanResource\LeaveRequests\EmployeeLeaveRequests;
use App\Livewire\HumanResource\LeaveRequests\ManagerLeaveRequests;
use App\Livewire\HumanResource\Messages\Bulk;
use App\Livewire\HumanResource\Messages\Personal;
use App\Livewire\HumanResource\Statistics;
use App\Livewire\HumanResource\Structure\Centers;
use App\Livewire\HumanResource\Structure\Departments;
use App\Livewire\HumanResource\Structure\EmployeeInfo;
use App\Livewire\HumanResource\Structure\Employees;
use App\Livewire\HumanResource\Structure\Positions;
use App\Livewire\MaintenanceMode;
use App\Livewire\Misc\ComingSoon;
use App\Livewire\SaaS\ClientDashboard;
use App\Livewire\SaaS\Clients;
use App\Livewire\SaaS\SuperAdminDashboard;
use App\Livewire\Settings\Users;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('lang/{locale}', [LanguageController::class, 'swap']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'allow_admin_during_maintenance',
])->group(function () {
    // 👉 Home Dispatcher
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // 👉 Super Admin Routes
    Route::group(['prefix' => 'super-admin'], function () {
        Route::get('/dashboard', SuperAdminDashboard::class)->middleware('check.action:view_dashboard_super_admin')->name('super-admin.dashboard');
        Route::get('/clients', Clients::class)->middleware('check.action:view_clients')->name('super-admin-clients');
        Route::get('/leaves', ManagerLeaveRequests::class)->middleware('check.action:view_leaves')->name('super-admin.leaves');
        Route::get('/holidays', HolidayManagement::class)->middleware('check.action:view_holidays')->name('super-admin.holidays');
        Route::get('/advances', \App\Livewire\HumanResource\Advances\AdvanceManagement::class)->middleware('check.action:manage_advances')->name('super-admin.advances');
        Route::get('/salaries', \App\Livewire\HumanResource\Salary\SalaryManagement::class)->middleware('check.action:view_employee_salary')->name('super-admin.salaries');
    });

    // 👉 Client Routes
    Route::group(['prefix' => 'client'], function () {
        Route::get('/dashboard', ClientDashboard::class)->middleware('check.action:view_dashboard_client')->name('client.dashboard');
        Route::get('/companies', \App\Livewire\SaaS\Companies::class)->middleware('check.action:view_companies')->name('client.companies');
        Route::get('/leaves', ManagerLeaveRequests::class)->middleware('check.action:view_leaves')->name('client.leaves');
        Route::get('/holidays', HolidayManagement::class)->middleware('check.action:view_holidays')->name('client.holidays');
        Route::get('/advances', \App\Livewire\HumanResource\Advances\AdvanceManagement::class)->middleware('check.action:view_client_advances')->name('client.advances');
        Route::get('/salaries', \App\Livewire\HumanResource\Salary\SalaryManagement::class)->middleware('check.action:view_employee_salary')->name('client.salaries');
    });

    // 👉 Company Routes
    Route::group(['prefix' => 'company'], function () {
        Route::get('/dashboard', Dashboard::class)->middleware('check.action:view_dashboard_company')->name('company.dashboard');
        Route::get('/leaves', ManagerLeaveRequests::class)->middleware('check.action:view_leaves')->name('company.leaves');
        Route::get('/holidays', HolidayManagement::class)->middleware('check.action:view_holidays')->name('company.holidays');
        Route::get('/advances', \App\Livewire\HumanResource\Advances\AdvanceManagement::class)->middleware('check.action:view_company_advances')->name('company.advances');
        Route::get('/advances/settings', \App\Livewire\HumanResource\Advances\AdvanceSettings::class)->middleware('check.action:manage_advance_settings')->name('company.advances.settings');
        Route::get('/salaries', \App\Livewire\HumanResource\Salary\SalaryManagement::class)->middleware('check.action:view_employee_salary')->name('company.salaries');
    });

    // 👉 Employee Routes
    Route::group(['prefix' => 'employee'], function () {
        Route::get('/dashboard', Dashboard::class)->middleware('check.action:view_dashboard_employee')->name('employee.dashboard');
        Route::get('/leaves', EmployeeLeaveRequests::class)->middleware('check.action:view_my_leaves')->name('employee.leaves');
        Route::get('/holidays', EmployeeHolidays::class)->middleware('check.action:view_my_holidays')->name('employee.holidays');
        Route::get('/advances', \App\Livewire\HumanResource\Advances\EmployeeAdvances::class)->middleware('check.action:view_my_advances')->name('employee.advances');
        Route::get('/salary', \App\Livewire\HumanResource\Salary\EmployeeSalary::class)->middleware('check.action:view_my_salary')->name('employee.salary');
    });

    // 👉 Dashboard
    Route::redirect('/', '/home');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // 👉 Human Resource
    Route::prefix('attendance')->group(function () {
        Route::get('/fingerprints', Fingerprints::class)->middleware('check.action:view_fingerprints')->name('attendance-fingerprints');
        Route::get('/leaves', Leaves::class)->middleware('check.action:manage_legacy_leaves')->name('attendance-leaves');
    });

    Route::prefix('structure')->group(function () {
        Route::get('/centers', Centers::class)->middleware('check.action:view_structure')->name('structure-centers');
        Route::get('/departments', Departments::class)->middleware('check.action:view_structure')->name('structure-departments');
        Route::get('/positions', Positions::class)->middleware('check.action:view_structure')->name('structure-positions');
        Route::get('/employees', Employees::class)->middleware('check.action:view_employees')->name('structure-employees');
        Route::get('/employee/{id?}', EmployeeInfo::class)->middleware('check.action:view_employees')->name('structure-employees-info');
    });

    Route::prefix('messages')->group(function () {
        Route::get('/bulk', Bulk::class)->middleware('check.action:view_messages')->name('messages-bulk');
        Route::get('/personal', Personal::class)->middleware('check.action:view_messages')->name('messages-personal');
    });

    Route::get('/discounts', Discounts::class)->middleware('check.action:view_discounts')->name('discounts');
    Route::get('/statistics', Statistics::class)->middleware('check.action:view_statistics')->name('statistics');

    Route::prefix('settings')->middleware('check.action:manage_settings')->group(function () {
        Route::get('/users', Users::class)->name('settings-users');
        Route::get('/roles', ComingSoon::class)->name('settings-roles');
        Route::get('/permissions', ComingSoon::class)->name('settings-permissions');
    });

    // 👉 Assets
    Route::prefix('assets')->group(function () {
        Route::get('/inventory', Inventory::class)->middleware('check.action:view_assets')->name('inventory');
        Route::get('/categories', Categories::class)->middleware('check.action:view_asset_categories')->name('categories');
        Route::get('/reports', ComingSoon::class)->middleware('check.action:view_asset_reports')->name('reports');
    });

    Route::get('/contact-us', ContactUs::class)->name('contact-us');

    Route::get('/maintenance-mode', MaintenanceMode::class)->name('maintenance-mode');
});

Route::webhooks('/deploy');
