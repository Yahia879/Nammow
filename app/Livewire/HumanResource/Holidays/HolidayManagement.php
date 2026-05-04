<?php

namespace App\Livewire\HumanResource\Holidays;

use App\Models\Company;
use App\Models\Holiday;
use App\Models\HolidayCompany;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class HolidayManagement extends Component
{
    use WithPagination;

    // Filters
    public $company_id = '';
    public $scope = '';
    public $date_from = '';
    public $date_to = '';
    public $created_by_type = '';
    public $search = '';

    // Create/Edit Form
    public $holiday_id;
    public $title;
    public $description;
    public $start_date;
    public $end_date;
    public $apply_to_all = false;
    public $selected_companies = []; // for selected scope
    public $client_id_form; // For super_admin to select client

    public $isEdit = false;

    protected $queryString = [
        'company_id' => ['except' => ''],
        'scope' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
        'created_by_type' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function render()
    {
        $user = Auth::user();
        $query = Holiday::with(['companies', 'companyManager', 'client'])
            ->latest();

        // Strict Tenant Isolation
        if ($user->hasAnyRole(['super_admin'])) {
            // Can see all
        } elseif ($user->hasRole('client')) {
            $query->where('client_id', $user->client_id);
        } elseif ($user->hasRole('company')) {
            // Managers can see:
            // 1. Holidays they created themselves
            // 2. Holidays explicitly assigned to their company
            // 3. Holidays applied to ALL companies under their client
            $query->where(function($q) use ($user) {
                $q->where('company_manager_id', $user->id)
                  ->orWhereHas('companies', function($sq) use ($user) {
                      $sq->where('company_id', $user->company_id);
                  })
                  ->orWhere(function($sq) use ($user) {
                      $sq->where('scope', 'all')
                        ->where('client_id', $user->client_id);
                  });
            });
        } else {
            abort(403);
        }

        // Apply Filters
        if ($this->company_id) {
            $query->where(function($q) {
                $q->whereHas('companies', function($sq) {
                    $sq->where('company_id', $this->company_id);
                })->orWhere('scope', 'all');
            });
        }
        if ($this->scope) $query->where('scope', $this->scope);
        if ($this->date_from) $query->where('start_date', '>=', $this->date_from);
        if ($this->date_to) $query->where('end_date', '<=', $this->date_to);
        if ($this->created_by_type) $query->where('created_by_type', $this->created_by_type);
        if ($this->search) $query->where('title', 'like', '%' . $this->search . '%');

        // Data for form
        $companies = collect([]);
        $clients = collect([]);
        if ($user->hasAnyRole(['super_admin'])) {
            $companies = Company::all();
            $clients = \App\Models\Client::all();
        } elseif ($user->hasRole('client')) {
            $companies = Company::where('client_id', $user->client_id)->get();
        } elseif ($user->hasRole('company')) {
            $companies = Company::where('id', $user->company_id)->get();
        }

        return view('livewire.human-resource.holidays.holiday-management', [
            'holidays' => $query->paginate(10),
            'availableCompanies' => $companies,
            'clients' => $clients,
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['company_id', 'scope', 'date_from', 'date_to', 'created_by_type', 'search']);
    }

    public function showCreateModal()
    {
        if (Auth::user()->hasAnyRole(['super_admin'])) {
            $this->dispatch('toastr', type: 'error', message: __('Unauthorized action. Super admins can only view holidays.'));
            return;
        }

        $this->reset(['holiday_id', 'title', 'description', 'start_date', 'end_date', 'apply_to_all', 'selected_companies', 'client_id_form']);
        $this->isEdit = false;
        
        if (Auth::user()->hasRole('company')) {
            $this->apply_to_all = false;
            $this->selected_companies = [Auth::user()->company_id];
        }

        $this->dispatch('openModal', elementId: '#holidayModal');
    }

    public function saveHoliday()
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['super_admin'])) {
            $this->dispatch('toastr', type: 'error', message: __('Unauthorized action. Super admins can only view holidays.'));
            return;
        }
        
        $rules = [
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];

        if ($user->hasAnyRole(['super_admin'])) {
            $rules['client_id_form'] = 'required|exists:clients,id';
        }

        if (($user->hasRole('client') || $user->hasAnyRole(['super_admin'])) && !$this->apply_to_all) {
            $rules['selected_companies'] = 'required|array|min:1';
        }

        $this->validate($rules);

        $data = [
            'client_id' => $user->hasAnyRole(['super_admin']) ? $this->client_id_form : $user->client_id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'scope' => $this->apply_to_all ? 'all' : 'selected',
            'created_by_type' => $user->hasAnyRole(['super_admin']) ? 'super_admin' : ($user->hasRole('client') ? 'client' : 'company_manager'),
            'company_manager_id' => $user->hasRole('company') ? $user->id : null,
        ];

        if ($this->isEdit) {
            $holiday = Holiday::findOrFail($this->holiday_id);
            
            // Authorization check for update
            if (!$user->hasAnyRole(['super_admin'])) {
                if ($user->hasRole('client') && ($holiday->client_id != $user->client_id || $holiday->created_by_type != 'client')) abort(403);
                if ($user->hasRole('company') && $holiday->company_manager_id != $user->id) abort(403);
            }

            $holiday->update($data);
            $holiday->holidayCompanies()->delete();
        } else {
            $holiday = Holiday::create($data);
        }

        // Attach companies
        if (!$this->apply_to_all) {
            foreach ($this->selected_companies as $coId) {
                HolidayCompany::create([
                    'holiday_id' => $holiday->id,
                    'company_id' => $coId,
                ]);
            }
        }

        $this->dispatch('closeModal', elementId: '#holidayModal');
        $this->dispatch('toastr', type: 'success', message: __('Holiday saved successfully.'));
    }

    public function showEditModal(Holiday $holiday)
    {
        $user = Auth::user();
        
        // Authorization check: Super admin can only view
        if ($user->hasAnyRole(['super_admin'])) {
            $this->dispatch('toastr', type: 'error', message: __('Unauthorized action. Super admins can only view holidays.'));
            return;
        }

        // Authorization check: Super admin can edit everything, others only creators
        if (!$user->hasAnyRole(['super_admin'])) {
            if ($user->hasRole('client')) {
                if ($holiday->client_id != $user->client_id || $holiday->created_by_type !== 'client') {
                    $this->dispatch('toastr', type: 'error', message: __('Unauthorized. Clients can only edit holidays they created.'));
                    return;
                }
            } elseif ($user->hasRole('company')) {
                if ($holiday->company_manager_id != $user->id) {
                    $this->dispatch('toastr', type: 'error', message: __('Unauthorized. You can only edit holidays you created.'));
                    return;
                }
            }
        }

        $this->isEdit = true;
        $this->holiday_id = $holiday->id;
        $this->title = $holiday->title;
        $this->description = $holiday->description;
        $this->start_date = $holiday->start_date->format('Y-m-d');
        $this->end_date = $holiday->end_date->format('Y-m-d');
        $this->apply_to_all = $holiday->scope === 'all';
        $this->selected_companies = $holiday->companies->pluck('id')->toArray();
        $this->client_id_form = $holiday->client_id;

        $this->dispatch('openModal', elementId: '#holidayModal');
    }

    public function confirmDelete($id)
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['super_admin'])) {
            $this->dispatch('toastr', type: 'error', message: __('Unauthorized action. Super admins can only view holidays.'));
            return;
        }

        $holiday = Holiday::findOrFail($id);

        // Pre-check authorization before showing confirmation
        if (!$user->hasAnyRole(['super_admin'])) {
            if ($user->hasRole('client')) {
                if ($holiday->client_id != $user->client_id || $holiday->created_by_type !== 'client') {
                    $this->dispatch('toastr', type: 'error', message: __('Unauthorized action.'));
                    return;
                }
            } elseif ($user->hasRole('company')) {
                if ($holiday->company_manager_id != $user->id) {
                    $this->dispatch('toastr', type: 'error', message: __('Unauthorized action.'));
                    return;
                }
            }
        }

        $this->dispatch('show-delete-confirmation', 
            title: __('Delete Holiday?'),
            text: __('This holiday will be permanently removed.'),
            method: 'delete-holiday-confirmed',
            id: $id
        );
    }

    #[On('delete-holiday-confirmed')]
    public function deleteHoliday($id)
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['super_admin'])) {
            abort(403);
        }

        $holiday = Holiday::findOrFail($id);

        // Final Authorization check
        if (!$user->hasAnyRole(['super_admin'])) {
            if ($user->hasRole('client')) {
                if ($holiday->client_id != $user->client_id || $holiday->created_by_type !== 'client') abort(403);
            } elseif ($user->hasRole('company')) {
                if ($holiday->company_manager_id != $user->id) abort(403);
            }
        }

        $holiday->delete();
        $this->dispatch('toastr', type: 'success', message: __('Holiday deleted.'));
    }
}
