<?php

namespace App\Livewire\HumanResource\LeaveRequests;

use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ManagerLeaveRequests extends Component
{
    use WithPagination;

    // Filters
    public $status = '';
    public $employee_id = '';
    public $company_id = '';
    public $leave_type_id = '';
    public $date_from = '';
    public $date_to = '';
    public $search = '';

    // Action Form
    public $selected_request_id;
    public $rejection_reason;

    protected $queryString = [
        'status' => ['except' => ''],
        'employee_id' => ['except' => ''],
        'company_id' => ['except' => ''],
        'leave_type_id' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function render()
    {
        $user = Auth::user();
        $query = LeaveRequest::with(['employee', 'company', 'leaveType'])
            ->latest();

        // Strict Tenant Isolation
        if ($user->hasAnyRole(['super_admin'])) {
            // Super admin sees all
        } elseif ($user->hasRole('client')) {
            $query->where('client_id', $user->client_id);
        } elseif ($user->hasRole('company')) {
            $query->where('company_id', $user->company_id);
        } else {
            abort(403);
        }

        // Apply Filters
        if ($this->status) $query->where('status', $this->status);
        if ($this->employee_id) $query->where('employee_id', $this->employee_id);
        if ($this->company_id) $query->where('company_id', $this->company_id);
        if ($this->leave_type_id) $query->where('leave_type_id', $this->leave_type_id);
        if ($this->date_from) $query->where('start_date', '>=', $this->date_from);
        if ($this->date_to) $query->where('end_date', '<=', $this->date_to);
        if ($this->search) {
            $query->whereHas('employee', function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%');
            });
        }

        // Data for filters
        $companies = collect([]);
        $employees = collect([]);

        if ($user->hasAnyRole(['super_admin'])) {
            $companies = Company::all();
            $employees = Employee::all();
        } elseif ($user->hasRole('client')) {
            $companies = Company::where('client_id', $user->client_id)->get();
            $employees = Employee::whereHas('company', function($q) use ($user) {
                $q->where('client_id', $user->client_id);
            })->get();
        } elseif ($user->hasRole('company')) {
            $employees = Employee::where('company_id', $user->company_id)->get();
        }

        return view('livewire.human-resource.leave-requests.manager-leave-requests', [
            'leaveRequests' => $query->paginate(10),
            'leaveTypes' => LeaveType::all(),
            'companies' => $companies,
            'employees' => $employees,
            'selectedEmployee' => $this->employee_id ? Employee::find($this->employee_id) : null,
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['status', 'employee_id', 'company_id', 'leave_type_id', 'date_from', 'date_to', 'search']);
    }

    public function approveRequest($id)
    {
        $user = Auth::user();

        $request = LeaveRequest::with(['leaveType', 'employee'])->findOrFail($id);

        // Strict Tenant Isolation
        if ($user->hasRole('company') && $request->company_id != $user->company_id) abort(403);
        if ($user->hasRole('client') && $request->client_id != $user->client_id) abort(403);

        $updateData = [
            'status' => 'approved',
            'decision_at' => now(),
        ];

        if ($user->hasAnyRole(['super_admin'])) {
            $updateData['decision_by_type'] = 'super_admin';
            $updateData['decision_company_manager_id'] = $user->id;
        } elseif ($user->hasRole('client')) {
            $updateData['decision_by_type'] = 'client';
            $updateData['decision_client_id'] = $user->id;
        } else {
            $updateData['decision_by_type'] = 'company_manager';
            $updateData['decision_company_manager_id'] = $user->id;
        }

        $request->update($updateData);

        // Deduct from annual leave balance if it's annual leave
        if ($request->leaveType && $request->leaveType->name === 'Annual Leave') {
            $request->employee->increment('taken_annual_leave_days', $request->total_days);
        }

        $this->dispatch('toastr', type: 'success', message: __('Leave request approved.'));
    }

    public function openRejectModal($id)
    {
        $this->selected_request_id = $id;
        $this->rejection_reason = '';
        $this->dispatch('openModal', elementId: '#rejectModal');
    }

    public function rejectRequest()
    {
        $user = Auth::user();

        $this->validate([
            'rejection_reason' => 'required|string|min:5',
        ]);

        $request = LeaveRequest::findOrFail($this->selected_request_id);

        // Strict Tenant Isolation
        if ($user->hasRole('company') && $request->company_id != $user->company_id) abort(403);
        if ($user->hasRole('client') && $request->client_id != $user->client_id) abort(403);

        $updateData = [
            'status' => 'rejected',
            'rejection_reason' => $this->rejection_reason,
            'decision_at' => now(),
        ];

        if ($user->hasAnyRole(['super_admin'])) {
            $updateData['decision_by_type'] = 'super_admin';
            $updateData['decision_company_manager_id'] = $user->id;
        } elseif ($user->hasRole('client')) {
            $updateData['decision_by_type'] = 'client';
            $updateData['decision_client_id'] = $user->id;
        } else {
            $updateData['decision_by_type'] = 'company_manager';
            $updateData['decision_company_manager_id'] = $user->id;
        }

        $request->update($updateData);

        $this->dispatch('closeModal', elementId: '#rejectModal');
        $this->dispatch('toastr', type: 'success', message: __('Leave request rejected.'));
    }
}
