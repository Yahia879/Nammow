<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class Employees extends Component
{
    use WithPagination;

    // 👉 Variables
    public $searchTerm = null;

    public $contracts;

    public $employee;

    public $employeeInfo = [];

    public $isEdit = false;

    public $confirmedId;

    // 👉 Mount
    public function mount()
    {
        $this->contracts = Contract::all();
    }

    // 👉 Render
    public function render()
    {
        $employees = Employee::where('id', 'like', '%'.$this->searchTerm.'%')
            ->orWhere('full_name', 'like', '%'.$this->searchTerm.'%')
            ->orWhere('first_name', 'like', '%'.$this->searchTerm.'%')
            ->orWhere('last_name', 'like', '%'.$this->searchTerm.'%')
            ->paginate(20);

        return view('livewire.human-resource.structure.employees', [
            'employees' => $employees,
        ]);
    }

    // 👉 Submit employee
    public function submitEmployee()
    {
        $this->validate([
            'employeeInfo.fullName' => 'required|string',
            'employeeInfo.mobileNumber' => 'required|numeric',
            'employeeInfo.basicSalary' => 'required|numeric',
            'employeeInfo.housingAllowance' => 'required|numeric',
            'employeeInfo.transportAllowance' => 'required|numeric',
            'employeeInfo.otherAllowances' => 'required|numeric',
            'employeeInfo.joinDate' => 'required|date',
            'employeeInfo.annualLeaveDays' => 'required|integer',
            'employeeInfo.gender' => 'required',
            'employeeInfo.address' => 'nullable',
            'employeeInfo.email' => 'required|email|unique:users,email,'.($this->isEdit ? $this->employee->user?->id : 'NULL'),
            'employeeInfo.password' => $this->isEdit ? 'nullable' : 'required|min:8|confirmed',
        ]);

        $this->employeeInfo['mobileNumber'] = (int) $this->employeeInfo['mobileNumber'];

        $this->isEdit ? $this->editEmployee() : $this->addEmployee();
    }

    protected function validationAttributes()
    {
        return [
            'employeeInfo.fullName' => __('الاسم الكامل'),
            'employeeInfo.mobileNumber' => __('رقم الجوال'),
            'employeeInfo.basicSalary' => __('الراتب الأساسي'),
            'employeeInfo.housingAllowance' => __('بدل السكن'),
            'employeeInfo.transportAllowance' => __('بدل النقل'),
            'employeeInfo.otherAllowances' => __('البدلات الأخرى'),
            'employeeInfo.joinDate' => __('تاريخ الالتحاق'),
            'employeeInfo.annualLeaveDays' => __('عدد الإجازات السنوية'),
            'employeeInfo.gender' => __('الجنس'),
            'employeeInfo.email' => __('البريد الإلكتروني'),
            'employeeInfo.password' => __('كلمة المرور'),
        ];
    }

    protected function messages()
    {
        return [
            'employeeInfo.fullName.required' => __('الاسم الكامل مطلوب'),
            'employeeInfo.mobileNumber.required' => __('رقم الجوال مطلوب'),
            'employeeInfo.basicSalary.required' => __('الراتب الأساسي مطلوب'),
            'employeeInfo.housingAllowance.required' => __('بدل السكن مطلوب'),
            'employeeInfo.transportAllowance.required' => __('بدل النقل مطلوب'),
            'employeeInfo.otherAllowances.required' => __('البدلات الأخرى مطلوبة'),
            'employeeInfo.joinDate.required' => __('تاريخ الالتحاق مطلوب'),
            'employeeInfo.annualLeaveDays.required' => __('عدد الإجازات السنوية مطلوب'),
            'employeeInfo.gender.required' => __('الجنس مطلوب'),
            'employeeInfo.email.required' => __('البريد الإلكتروني مطلوب'),
            'employeeInfo.password.required' => __('كلمة المرور مطلوبة'),
        ];
    }

    // 👉 Store employee
    public function showCreateEmployeeModal()
    {
        $this->reset('isEdit', 'employeeInfo');
    }

    public function addEmployee()
    {
        DB::beginTransaction();

        try {
            $createdEmployee = Employee::create([
                'full_name' => $this->employeeInfo['fullName'],
                'mobile_number' => $this->employeeInfo['mobileNumber'],
                'basic_salary' => $this->employeeInfo['basicSalary'],
                'housing_allowance' => $this->employeeInfo['housingAllowance'],
                'transport_allowance' => $this->employeeInfo['transportAllowance'],
                'other_allowances' => $this->employeeInfo['otherAllowances'],
                'join_date' => $this->employeeInfo['joinDate'],
                'max_leave_allowed' => $this->employeeInfo['annualLeaveDays'],
                'annual_leave_days' => $this->employeeInfo['annualLeaveDays'],
                'gender' => $this->employeeInfo['gender'],
                'address' => $this->employeeInfo['address'] ?? null,
                'profile_photo_path' => 'profile-photos/.default-photo.jpg',
            ]);

            $userEmail = $this->employeeInfo['email'] ?? 'emp-'.$createdEmployee->id.'@system.com';

            $user = User::create([
                'name' => $this->employeeInfo['fullName'],
                'employee_id' => $createdEmployee->id,
                'email' => $userEmail,
                'password' => Hash::make($this->employeeInfo['password']),
            ]);

            // Assign role
            $employeeRole = \App\Models\Role::where('name', 'employee')->first();
            if ($employeeRole) {
                $user->update(['role_id' => $employeeRole->id]);
            }

            DB::commit();

            $this->reset('employeeInfo');
            $this->dispatch('closeModal', elementId: '#employeeModal');
            $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 👉 Update employee
    public function showEditEmployeeModal(Employee $employee)
    {
        $this->isEdit = true;

        $this->employee = $employee;

        $this->employeeInfo['fullName'] = $employee->full_name;
        $this->employeeInfo['mobileNumber'] = $employee->mobile_number;
        $this->employeeInfo['basicSalary'] = $employee->basic_salary;
        $this->employeeInfo['housingAllowance'] = $employee->housing_allowance;
        $this->employeeInfo['transportAllowance'] = $employee->transport_allowance;
        $this->employeeInfo['otherAllowances'] = $employee->other_allowances;
        $this->employeeInfo['joinDate'] = $employee->join_date;
        $this->employeeInfo['annualLeaveDays'] = $employee->annual_leave_days ?: $employee->max_leave_allowed;
        $this->employeeInfo['gender'] = $employee->gender;
        $this->employeeInfo['address'] = $employee->address;
        $this->employeeInfo['email'] = $employee->user?->email;
    }

    public function editEmployee()
    {
        DB::beginTransaction();

        try {
            $this->employee->update([
                'full_name' => $this->employeeInfo['fullName'],
                'mobile_number' => $this->employeeInfo['mobileNumber'],
                'basic_salary' => $this->employeeInfo['basicSalary'],
                'housing_allowance' => $this->employeeInfo['housingAllowance'],
                'transport_allowance' => $this->employeeInfo['transportAllowance'],
                'other_allowances' => $this->employeeInfo['otherAllowances'],
                'join_date' => $this->employeeInfo['joinDate'],
                'max_leave_allowed' => $this->employeeInfo['annualLeaveDays'],
                'annual_leave_days' => $this->employeeInfo['annualLeaveDays'],
                'gender' => $this->employeeInfo['gender'],
                'address' => $this->employeeInfo['address'] ?? null,
            ]);

            if ($this->employee->user) {
                $userData = [
                    'name' => $this->employeeInfo['fullName'],
                ];

                if (isset($this->employeeInfo['email'])) {
                    $userData['email'] = $this->employeeInfo['email'];
                }

                $this->employee->user->update($userData);
            }

            DB::commit();

            $this->dispatch('closeModal', elementId: '#employeeModal');
            $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 👉 Delete employee
    public function confirmDeleteEmployee($id)
    {
        $this->confirmedId = $id;
    }

    public function deleteEmployee(Employee $employee)
    {
        $employee->delete();
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
    }
}
