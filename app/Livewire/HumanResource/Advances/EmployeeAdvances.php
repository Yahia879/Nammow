<?php

namespace App\Livewire\HumanResource\Advances;

use App\Models\Advance;
use App\Models\AdvanceSetting;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeAdvances extends Component
{
    use WithPagination;

    public $requested_amount;
    public $reason;
    public $showRequestModal = false;

    public function mount()
    {
        if (!Auth::user()->canAction('view_my_advances')) {
            abort(403);
        }
    }

    public function showRequestModal()
    {
        $this->reset(['requested_amount', 'reason']);
        $this->showRequestModal = true;
    }

    public function submitRequest()
    {
        $user = Auth::user();
        if (!$user->canAction('request_advance')) {
            abort(403);
        }

        $settings = AdvanceSetting::where('company_id', $user->company_id)->first();
        
        if (!$settings || !$settings->is_enabled) {
            $this->dispatch('toastr', type: 'error', message: __('Advances are currently disabled for your company.'));
            return;
        }

        // Validation for open balance
        if (!$settings->allow_new_advance_with_open_balance) {
            $hasOpenAdvance = Advance::where('employee_id', $user->employee_id)
                ->whereIn('status', ['pending', 'approved'])
                ->whereHas('installments', function($q) {
                    $q->whereIn('status', ['unpaid', 'deducted']);
                })->exists();

            if ($hasOpenAdvance) {
                $this->dispatch('toastr', type: 'error', message: __('You have an open advance balance. A new request is not allowed.'));
                return;
            }
        }

        $this->validate([
            'requested_amount' => 'required|numeric|min:1',
            'reason' => 'nullable|string|max:500',
        ], [], [
            'requested_amount' => __('Requested Amount'),
            'reason' => __('Reason'),
        ]);

        Advance::create([
            'employee_id' => $user->employee_id,
            'company_id' => $user->company_id,
            'requested_amount' => (float) $this->requested_amount,
            'reason' => $this->reason,
            'status' => 'pending',
        ]);

        $this->showRequestModal = false;
        $this->dispatch('closeModal', elementId: '#advanceRequestModal');
        $this->dispatch('toastr', type: 'success', message: __('Advance request submitted successfully.'));
    }

    public function render()
    {
        $advances = Advance::where('employee_id', Auth::user()->employee_id)
            ->with('installments')
            ->latest()
            ->paginate(10);

        return view('livewire.human-resource.advances.employee-advances', [
            'advances' => $advances
        ]);
    }
}
