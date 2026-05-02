<?php

namespace App\Livewire\HumanResource\Salary;

use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SalaryManagement extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $filter_company = '';

    // Form Properties
    public $employee_id;
    public $amount = 0;
    public $allowances = 0;
    public $tax = 0;
    public $insurance = 0;
    public $effective_date;
    public $isEdit = false;

    public function mount()
    {
        if (!Auth::user()->canAction('view_employee_salary')) {
            abort(403);
        }
    }

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function resetInputs()
    {
        $this->employee_id = null;
        $this->amount = 0;
        $this->allowances = 0;
        $this->tax = 0;
        $this->insurance = 0;
        $this->effective_date = null;
        $this->isEdit = false;
        $this->resetValidation();
    }

    public function editSalary($employeeId)
    {
        if (!Auth::user()->canAction('manage_employee_salary')) {
            abort(403);
        }

        $this->resetInputs();
        
        $employee = Employee::findOrFail($employeeId);
        $this->employee_id = $employeeId;
        $salary = $employee->salary;

        if ($salary) {
            $this->amount = (float) $salary->amount;
            $this->allowances = (float) $salary->allowances;
            $this->tax = (float) $salary->tax;
            $this->insurance = (float) $salary->insurance;
            $this->effective_date = $salary->effective_date ? $salary->effective_date->format('Y-m-d') : null;
            $this->isEdit = true;
        }

        $this->dispatch('openModal', elementId: '#salaryModal');
    }

    public function saveSalary()
    {
        if (!Auth::user()->canAction('manage_employee_salary')) {
            abort(403);
        }

        $this->validate([
            'amount' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'insurance' => 'nullable|numeric|min:0',
            'effective_date' => 'nullable|date',
        ]);

        $employee = Employee::findOrFail($this->employee_id);

        Salary::create([
            'employee_id' => $this->employee_id,
            'company_id' => $employee->company_id,
            'amount' => (float) $this->amount,
            'allowances' => (float) ($this->allowances ?: 0),
            'tax' => (float) ($this->tax ?: 0),
            'insurance' => (float) ($this->insurance ?: 0),
            'effective_date' => $this->effective_date ?: now()->format('Y-m-d'),
        ]);

        $this->resetInputs();
        $this->dispatch('closeModal', elementId: '#salaryModal');
        $this->dispatch('toastr', type: 'success', message: __('Salary updated successfully.'));
    }

    public function getNetSalaryProperty()
    {
        return (float) ($this->amount ?: 0) + 
               (float) ($this->allowances ?: 0) - 
               (float) ($this->tax ?: 0) - 
               (float) ($this->insurance ?: 0);
    }

    public function render()
    {
        $user = Auth::user();
        $query = Employee::with(['salary', 'company']);

        // SaaS Isolation
        if ($user->hasRole('super_admin')) {
            // No restriction
        } elseif ($user->hasRole('client')) {
            $query->whereHas('company', function($q) use ($user) {
                $q->where('client_id', $user->client_id);
            });
        }

        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('first_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('last_name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('national_number', 'like', '%' . $this->searchTerm . '%');
            });
        }

        if ($this->filter_company) {
            $query->where('company_id', $this->filter_company);
        }

        $companies = collect();
        if ($user->hasAnyRole(['super_admin', 'client'])) {
            if ($user->hasRole('super_admin')) {
                $companies = \App\Models\Company::all();
            } else {
                $companies = \App\Models\Company::where('client_id', $user->client_id)->get();
            }
        }

        return view('livewire.human-resource.salary.salary-management', [
            'employees' => $query->paginate(10),
            'companies' => $companies,
        ]);
    }
}
