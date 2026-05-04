<?php

namespace App\Livewire\HumanResource\Holidays;

use App\Models\Holiday;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeHolidays extends Component
{
    use WithPagination;

    public $search = '';
    public $date_from = '';
    public $date_to = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
    ];

    public function render()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return view('livewire.human-resource.holidays.employee-holidays', [
                'holidays' => collect([]),
                'error' => 'No employee record found.'
            ]);
        }

        $query = Holiday::where('client_id', $user->client_id)
            ->where(function($q) use ($employee) {
                $q->where('scope', 'all')
                  ->orWhereHas('companies', function($sq) use ($employee) {
                      $sq->where('company_id', $employee->company_id);
                  });
            })
            ->latest();

        if ($this->search) $query->where('title', 'like', '%' . $this->search . '%');
        if ($this->date_from) $query->where('start_date', '>=', $this->date_from);
        if ($this->date_to) $query->where('end_date', '<=', $this->date_to);

        return view('livewire.human-resource.holidays.employee-holidays', [
            'holidays' => $query->paginate(10),
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'date_from', 'date_to']);
    }
}
