<?php

namespace App\Livewire\HumanResource\Salary;

use App\Models\Salary;
use App\Models\Discount;
use App\Models\AdvanceInstallment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeSalary extends Component
{
    use WithPagination;

    public $selectedDate;

    public function mount()
    {
        if (!Auth::user()->canAction('view_my_salary')) {
            abort(403);
        }
        $this->selectedDate = Carbon::now()->startOfMonth()->format('Y-m-d');
    }

    public function previousMonth()
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subMonth()->format('Y-m-d');
    }

    public function nextMonth()
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->addMonth()->format('Y-m-d');
    }

    public function render()
    {
        $employeeId = Auth::user()->employee_id;
        
        // Get the latest salary structure for the breakdown
        $latestSalary = Salary::where('employee_id', $employeeId)->latest('effective_date')->first();
        
        // Get history for the table (Official rate changes)
        $salaryHistory = Salary::where('employee_id', $employeeId)
            ->latest('effective_date')
            ->paginate(5, pageName: 'history');

        $current = Carbon::parse($this->selectedDate);
        $startDate = (clone $current)->startOfMonth();
        $endDate = (clone $current)->endOfMonth();

        // Variable Monthly Details
        $discounts = Discount::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        $totalDiscounts = $discounts->sum('amount');

        $installments = AdvanceInstallment::whereHas('advance', function($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            })
            ->whereBetween('due_date', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'deducted'])
            ->get();
        $totalInstallments = $installments->sum('amount');

        $netResult = $latestSalary ? ($latestSalary->net_salary - $totalDiscounts - $totalInstallments) : 0;

        return view('livewire.human-resource.salary.employee-salary', [
            'salary' => $latestSalary,
            'salaryHistory' => $salaryHistory,
            'discounts' => $discounts,
            'installments' => $installments,
            'totalDiscounts' => $totalDiscounts,
            'totalInstallments' => $totalInstallments,
            'netResult' => $netResult,
            'monthName' => $current->translatedFormat('F Y'),
        ]);
    }
}
