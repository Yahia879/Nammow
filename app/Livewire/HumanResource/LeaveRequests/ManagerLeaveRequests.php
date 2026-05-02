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
    public $decision_by_type = '';
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
        'decision_by_type' => ['except' => ''],
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
        if ($this->decision_by_type) $query->where('decision_by_type', $this->decision_by_type);
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
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['status', 'employee_id', 'company_id', 'leave_type_id', 'date_from', 'date_to', 'decision_by_type', 'search']);
    }

    public function approveRequest($id)
    {
        $user = Auth::user();

        // Clients can only review, not approve
        if ($user->hasRole('client')) {
            $this->dispatch('toastr', type: 'error', message: __('Unauthorized action. Clients can only review requests.'));
            return;
        }

        $request = LeaveRequest::findOrFail($id);

        // Authorization check
        if ($user->hasRole('company') && $request->company_id != $user->company_id) abort(403);

        $request->update([
            'status' => 'approved',
            'decision_at' => now(),
            'decision_by_type' => $user->hasAnyRole(['super_admin']) ? 'super_admin' : 'company_manager',
            'decision_company_manager_id' => $user->id,
        ]);

        $this->dispatch('toastr', type: 'success', message: __('Leave request approved.'));
    }

    public function openRejectModal($id)
    {
        $user = Auth::user();

        // Clients can only review, not reject
        if ($user->hasRole('client')) {
            $this->dispatch('toastr', type: 'error', message: __('Unauthorized action. Clients can only review requests.'));
            return;
        }

        $this->selected_request_id = $id;
        $this->rejection_reason = '';
        $this->dispatch('openModal', elementId: '#rejectModal');
    }

    public function rejectRequest()
    {
        $user = Auth::user();

        // Clients can only review, not reject
        if ($user->hasRole('client')) {
            $this->dispatch('toastr', type: 'error', message: __('Unauthorized action.'));
            return;
        }

        $this->validate([
            'rejection_reason' => 'required|string|min:5',
        ]);

        $request = LeaveRequest::findOrFail($this->selected_request_id);

        // Authorization check
        if ($user->hasRole('company') && $request->company_id != $user->company_id) abort(403);

        $request->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejection_reason,
            'decision_at' => now(),
            'decision_by_type' => $user->hasAnyRole(['super_admin']) ? 'super_admin' : 'company_manager',
            'decision_company_manager_id' => $user->id,
        ]);

        $this->dispatch('closeModal', elementId: '#rejectModal');
        $this->dispatch('toastr', type: 'success', message: __('Leave request rejected.'));
    }
}
