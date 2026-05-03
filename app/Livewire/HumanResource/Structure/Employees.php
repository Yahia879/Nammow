<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Contract;
use App\Models\Employee;
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
            'employeeInfo.id' => 'required',
            'employeeInfo.fullName' => 'required',
            'employeeInfo.mobileNumber' => 'required|min:9|max:9|regex:/^[1-9][0-9]*$/',
            'employeeInfo.basicSalary' => 'required|numeric',
            'employeeInfo.housingAllowance' => 'required|numeric',
            'employeeInfo.transportAllowance' => 'required|numeric',
            'employeeInfo.otherAllowances' => 'required|numeric',
            'employeeInfo.joinDate' => 'required|date',
            'employeeInfo.annualLeaveDays' => 'required|integer',
            'employeeInfo.gender' => 'required',
            'employeeInfo.address' => 'nullable',
        ]);

        $this->isEdit ? $this->editEmployee() : $this->addEmployee();
    }

    // 👉 Store employee
    public function showCreateEmployeeModal()
    {
        $this->reset('isEdit', 'employeeInfo');
    }

    public function addEmployee()
    {
        $createdEmployee = Employee::create([
            'id' => $this->employeeInfo['id'],
            'full_name' => $this->employeeInfo['fullName'],
            'mobile_number' => $this->employeeInfo['mobileNumber'],
            'basic_salary' => $this->employeeInfo['basicSalary'],
            'housing_allowance' => $this->employeeInfo['housingAllowance'],
            'transport_allowance' => $this->employeeInfo['transportAllowance'],
            'other_allowances' => $this->employeeInfo['otherAllowances'],
            'join_date' => $this->employeeInfo['joinDate'],
            'max_leave_allowed' => $this->employeeInfo['annualLeaveDays'],
            'gender' => $this->employeeInfo['gender'],
            'address' => $this->employeeInfo['address'] ?? null,
            'profile_photo_path' => 'profile-photos/.default-photo.jpg',
        ]);

        $this->dispatch('closeModal', elementId: '#employeeModal');
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));

        session()->flash('openTimelineModal', true);

        return redirect()->route('structure-employees-info', ['id' => $createdEmployee->id]);
    }

    // 👉 Update employee
    public function showEditEmployeeModal(Employee $employee)
    {
        $this->isEdit = true;

        $this->employee = $employee;

        $this->employeeInfo['id'] = $employee->id;
        $this->employeeInfo['fullName'] = $employee->full_name;
        $this->employeeInfo['mobileNumber'] = $employee->mobile_number;
        $this->employeeInfo['basicSalary'] = $employee->basic_salary;
        $this->employeeInfo['housingAllowance'] = $employee->housing_allowance;
        $this->employeeInfo['transportAllowance'] = $employee->transport_allowance;
        $this->employeeInfo['otherAllowances'] = $employee->other_allowances;
        $this->employeeInfo['joinDate'] = $employee->join_date;
        $this->employeeInfo['annualLeaveDays'] = $employee->max_leave_allowed;
        $this->employeeInfo['gender'] = $employee->gender;
        $this->employeeInfo['address'] = $employee->address;
    }

    public function editEmployee()
    {
        $this->employee->update([
            'id' => $this->employeeInfo['id'],
            'full_name' => $this->employeeInfo['fullName'],
            'mobile_number' => $this->employeeInfo['mobileNumber'],
            'basic_salary' => $this->employeeInfo['basicSalary'],
            'housing_allowance' => $this->employeeInfo['housingAllowance'],
            'transport_allowance' => $this->employeeInfo['transportAllowance'],
            'other_allowances' => $this->employeeInfo['otherAllowances'],
            'join_date' => $this->employeeInfo['joinDate'],
            'max_leave_allowed' => $this->employeeInfo['annualLeaveDays'],
            'gender' => $this->employeeInfo['gender'],
            'address' => $this->employeeInfo['address'] ?? null,
        ]);

        $this->dispatch('closeModal', elementId: '#employeeModal');
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
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
