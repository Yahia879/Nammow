<?php

namespace App\Livewire\HumanResource\Advances;

use App\Models\Advance;
use App\Models\AdvanceInstallment;
use App\Models\AdvanceSetting;
use App\Models\Company;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AdvanceManagement extends Component
{
    use WithPagination;

    // Filters
    public $filter_status = '';
    public $filter_employee = '';
    public $filter_company = '';
    public $filter_date_from = '';
    public $filter_date_to = '';

    // Approval Form
    public $selectedAdvance;
    public $approved_amount;
    public $number_of_installments;
    public $rejection_reason;

    public function mount()
    {
        $user = Auth::user();
        if (!$user->canAction('view_company_advances') && 
            !$user->canAction('view_client_advances') && 
            !$user->canAction('manage_advances')) {
            abort(403);
        }
    }

    public function selectAdvance($id)
    {
        $this->selectedAdvance = Advance::with('employee', 'company')->findOrFail($id);
        $this->approved_amount = $this->selectedAdvance->requested_amount;
        $this->number_of_installments = 1;
    }

    public function approve()
    {
        $user = Auth::user();
        if (!$user->canAction('approve_advance')) {
            abort(403);
        }

        // Ownership check for Client
        if ($user->hasRole('client')) {
            if ($this->selectedAdvance->company->client_id != $user->client_id) {
                abort(403, 'You do not own this company.');
            }
        }

        $this->validate([
            'approved_amount' => 'required|numeric|min:1',
            'number_of_installments' => 'required|integer|min:1',
        ]);

        $settings = AdvanceSetting::where('company_id', $this->selectedAdvance->company_id)->first();
        if ($settings) {
            if ($this->number_of_installments > $settings->max_installments) {
                $this->addError('number_of_installments', __('Max installments allowed is :max', ['max' => $settings->max_installments]));
                return;
            }

            if ($settings->max_advance_type === 'fixed' && $settings->max_advance_value > 0 && $this->approved_amount > $settings->max_advance_value) {
                $this->addError('approved_amount', __('Max advance amount allowed is :max', ['max' => number_format($settings->max_advance_value, 2)]));
                return;
            }

            if ($settings->max_advance_type === 'percentage' && $settings->max_advance_value > 0) {
                $employeeSalary = $this->selectedAdvance->employee->salary;
                if (!$employeeSalary) {
                    $this->addError('approved_amount', __('Employee salary not set. Cannot calculate percentage limit.'));
                    return;
                }
                $maxAllowed = ($employeeSalary->amount * $settings->max_advance_value) / 100;
                if ($this->approved_amount > $maxAllowed) {
                    $this->addError('approved_amount', __('Max advance amount allowed is :max (:percent% of salary)', [
                        'max' => number_format($maxAllowed, 2),
                        'percent' => $settings->max_advance_value
                    ]));
                    return;
                }
            }
        }

        $this->selectedAdvance->update([
            'approved_amount' => $this->approved_amount,
            'number_of_installments' => $this->number_of_installments,
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $this->generateInstallments();

        $this->dispatch('closeModal', elementId: '#approveModal');
        $this->dispatch('toastr', type: 'success', message: __('Advance approved and installments generated.'));
        $this->reset(['selectedAdvance', 'approved_amount', 'number_of_installments']);
    }

    public function reject()
    {
        $user = Auth::user();
        if (!$user->canAction('reject_advance')) {
            abort(403);
        }

        // Ownership check for Client
        if ($user->hasRole('client')) {
            if ($this->selectedAdvance->company->client_id != $user->client_id) {
                abort(403, 'You do not own this company.');
            }
        }

        $this->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $this->selectedAdvance->update([
            'status' => 'rejected',
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'rejection_reason' => $this->rejection_reason,
        ]);

        $this->dispatch('closeModal', elementId: '#rejectModal');
        $this->dispatch('toastr', type: 'info', message: __('Advance request rejected.'));
        $this->reset(['selectedAdvance', 'rejection_reason']);
    }

    private function generateInstallments()
    {
        $amountPerInstallment = round($this->approved_amount / $this->number_of_installments, 2);
        $lastInstallmentAmount = $this->approved_amount - ($amountPerInstallment * ($this->number_of_installments - 1));

        // Logic for due_date: Assuming salary is at the end of the month
        // We start from the current or next month depending on the date
        $startDate = Carbon::now()->addMonthNoOverflow()->startOfMonth();

        for ($i = 1; $i <= $this->number_of_installments; $i++) {
            $dueDate = (clone $startDate)->addMonthsNoOverflow($i - 1)->endOfMonth();
            
            AdvanceInstallment::create([
                'advance_id' => $this->selectedAdvance->id,
                'company_id' => $this->selectedAdvance->company_id,
                'amount' => ($i === $this->number_of_installments) ? $lastInstallmentAmount : $amountPerInstallment,
                'due_date' => $dueDate,
                'status' => 'unpaid',
            ]);
        }
    }

    public function render()
    {
        $user = Auth::user();
        $query = Advance::with(['employee', 'company', 'installments']);

        // SaaS Isolation handled by BelongsToCompany trait global scope for Company users
        // For Client and SuperAdmin, we might need manual checks if the trait isn't enough or role-specific
        if ($user->hasRole('client')) {
            $query->whereHas('company', function($q) use ($user) {
                $q->where('client_id', $user->client_id);
            });
        } elseif ($user->hasRole('super_admin')) {
            // No restriction
        }

        // Filters
        if ($this->filter_status) $query->where('status', $this->filter_status);
        if ($this->filter_employee) $query->where('employee_id', $this->filter_employee);
        if ($this->filter_company) $query->where('company_id', $this->filter_company);
        if ($this->filter_date_from) $query->where('created_at', '>=', $this->filter_date_from);
        if ($this->filter_date_to) $query->where('created_at', '<=', $this->filter_date_to);

        $companies = collect();
        if ($user->hasRole('super_admin')) {
            $companies = Company::all();
        } elseif ($user->hasRole('client')) {
            $companies = Company::where('client_id', $user->client_id)->get();
        }

        return view('livewire.human-resource.advances.advance-management', [
            'advances' => $query->latest()->paginate(10),
            'companies' => $companies,
            'employees' => Employee::all(), // Should be filtered in blade based on company if needed
        ]);
    }
}
