<?php

namespace App\Livewire\HumanResource\LeaveRequests;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeLeaveRequests extends Component
{
    use WithPagination;

    // Filters
    public $status = '';
    public $leave_type_id = '';
    public $date_from = '';
    public $date_to = '';
    public $search = '';

    // Create Form
    public $new_leave_type_id;
    public $new_start_date;
    public $new_end_date;
    public $new_reason;

    protected $queryString = [
        'status' => ['except' => ''],
        'leave_type_id' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function render()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return view('livewire.human-resource.leave-requests.employee-leave-requests', [
                'leaveRequests' => collect([]),
                'leaveTypes' => collect([]),
                'error' => __('You are not linked to an employee record.')
            ]);
        }

        $query = LeaveRequest::where('employee_id', $employee->id)
            ->with('leaveType')
            ->latest();

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->leave_type_id) {
            $query->where('leave_type_id', $this->leave_type_id);
        }

        if ($this->date_from) {
            $query->where('start_date', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->where('end_date', '<=', $this->date_to);
        }

        if ($this->search) {
            $query->where('reason', 'like', '%' . $this->search . '%');
        }

        return view('livewire.human-resource.leave-requests.employee-leave-requests', [
            'leaveRequests' => $query->paginate(10),
            'leaveTypes' => LeaveType::where('is_active', true)->get(),
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['status', 'leave_type_id', 'date_from', 'date_to', 'search']);
    }

    public function createLeaveRequest()
    {
        $user = Auth::user();
        $employee = $user->employee;

        $this->validate([
            'new_leave_type_id' => 'required|exists:leave_types,id',
            'new_start_date' => 'required|date',
            'new_end_date' => 'required|date|after_or_equal:new_start_date',
            'new_reason' => 'required|string|min:10',
        ]);

        // Calculate total days
        $start = Carbon::parse($this->new_start_date);
        $end = Carbon::parse($this->new_end_date);
        $totalDays = $start->diffInDays($end) + 1;

        // Prevent overlapping
        $overlap = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->new_start_date, $this->new_end_date])
                    ->orWhereBetween('end_date', [$this->new_start_date, $this->new_end_date])
                    ->orWhere(function ($q) {
                        $q->where('start_date', '<=', $this->new_start_date)
                            ->where('end_date', '>=', $this->new_end_date);
                    });
            })
            ->exists();

        if ($overlap) {
            $this->addError('new_start_date', __('You already have a pending or approved leave request for this period.'));
            return;
        }

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'company_id' => $employee->company_id,
            'client_id' => $user->client_id,
            'leave_type_id' => $this->new_leave_type_id,
            'start_date' => $this->new_start_date,
            'end_date' => $this->new_end_date,
            'total_days' => $totalDays,
            'reason' => $this->new_reason,
            'status' => 'pending',
        ]);

        $this->reset(['new_leave_type_id', 'new_start_date', 'new_end_date', 'new_reason']);
        $this->dispatch('closeModal', elementId: '#createLeaveModal');
        $this->dispatch('toastr', type: 'success', message: __('Leave request submitted successfully.'));
    }

    public function deleteRequest($id)
    {
        $request = LeaveRequest::where('employee_id', Auth::user()->employee->id)->findOrFail($id);
        
        if ($request->status !== 'pending') {
            $this->dispatch('toastr', type: 'error', message: __('You can only delete pending requests.'));
            return;
        }

        $request->delete();
        $this->dispatch('toastr', type: 'success', message: __('Leave request deleted.'));
    }
}
