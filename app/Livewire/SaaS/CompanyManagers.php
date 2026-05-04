<?php

namespace App\Livewire\SaaS;

use App\Models\Company;
use App\Models\CompanyManager;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class CompanyManagers extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Search and Filter properties
    public $searchTerm = '';
    public $companySearch = '';
    public $filterCompany = '';

    // Form properties
    public $managerId;
    public $name;
    public $email;
    public $phone;
    public $password;
    public $password_confirmation;
    public $selected_companies = [];

    public $isEdit = false;
    public $confirmedId;

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function updatingFilterCompany()
    {
        $this->resetPage();
    }

    public function resetInputs()
    {
        $this->reset(['managerId', 'name', 'email', 'phone', 'password', 'password_confirmation', 'selected_companies', 'isEdit', 'confirmedId', 'companySearch', 'filterCompany']);
    }

    public function showCreateManagerModal()
    {
        $this->resetInputs();
        $this->isEdit = false;
    }

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:users,email,' . ($this->managerId ? CompanyManager::find($this->managerId)?->user_id : 'NULL'),
            'phone' => 'nullable',
            'selected_companies' => 'required|array|min:1',
            'selected_companies.*' => 'exists:companies,id,client_id,' . auth()->user()->client_id,
        ];

        if (!$this->isEdit) {
            $rules['password'] = 'required|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|min:8|confirmed';
        }

        return $rules;
    }

    public function store()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'mobile' => $this->phone ?: null,
                'password' => Hash::make($this->password),
                'client_id' => auth()->user()->client_id,
                'company_id' => $this->selected_companies[0] ?? null,
            ]);

            // Assign role
            $companyRole = \App\Models\Role::where('name', 'company')->first();
            if ($companyRole) {
                $user->update(['role_id' => $companyRole->id]);
            }

            $manager = CompanyManager::create([
                'user_id' => $user->id,
                'role' => 'company',
                'status' => 'active',
            ]);

            $manager->companies()->sync($this->selected_companies);

            DB::commit();

            $this->resetInputs();
            $this->dispatch('closeModal', elementId: '#managerModal');
            $this->dispatch('toastr', type: 'success', message: __('Manager created successfully!'));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toastr', type: 'error', message: __('Error: ') . $e->getMessage());
        }
    }

    public function editManager($id)
    {
        $this->resetInputs();
        $this->isEdit = true;
        $this->managerId = $id;

        $manager = CompanyManager::with(['user', 'companies'])->findOrFail($id);
        
        // Ensure isolation: manager must belong to current client (via user)
        if ($manager->user->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        $this->name = $manager->user->name;
        $this->email = $manager->user->email;
        $this->phone = $manager->user->mobile;
        $this->selected_companies = $manager->companies->pluck('id')->toArray();
    }

    public function update()
    {
        $this->validate();

        $manager = CompanyManager::with('user')->findOrFail($this->managerId);

        // Ensure isolation
        if ($manager->user->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
                'mobile' => $this->phone ?: null,
                'company_id' => $this->selected_companies[0] ?? null,
            ];

            if ($this->password) {
                $userData['password'] = Hash::make($this->password);
            }

            $manager->user->update($userData);

            $manager->companies()->sync($this->selected_companies);

            DB::commit();

            $this->resetInputs();
            $this->dispatch('closeModal', elementId: '#managerModal');
            $this->dispatch('toastr', type: 'success', message: __('Manager updated successfully!'));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toastr', type: 'error', message: __('Error: ') . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->confirmedId = $id;
    }

    public function deleteManager($id)
    {
        $manager = CompanyManager::with('user')->findOrFail($id);

        // Ensure isolation
        if ($manager->user->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // We force delete the user to avoid email conflicts and fully remove the manager
            if ($manager->user) {
                $manager->user->forceDelete();
            }
            $manager->delete();

            DB::commit();
            $this->dispatch('toastr', type: 'success', message: __('Manager deleted successfully!'));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toastr', type: 'error', message: __('Error: ') . $e->getMessage());
        }
        
        $this->confirmedId = null;
    }

    public function render()
    {
        $managers = CompanyManager::whereHas('user', function ($query) {
                $query->where('client_id', auth()->user()->client_id)
                    ->where(function($q) {
                        $q->where('name', 'like', '%' . $this->searchTerm . '%')
                          ->orWhere('email', 'like', '%' . $this->searchTerm . '%');
                    });
            })
            ->when($this->filterCompany, function ($query) {
                $query->whereHas('companies', function ($q) {
                    $q->where('companies.id', $this->filterCompany);
                });
            })
            ->with(['user', 'companies'])
            ->paginate(10);

        $companies = Company::where('client_id', auth()->user()->client_id)
            ->where('is_active', true)
            ->when($this->companySearch, function($query) {
                $query->where('name', 'like', '%' . $this->companySearch . '%');
            })
            ->get();

        return view('livewire.saa-s.company-managers', [
            'managers' => $managers,
            'companies' => $companies,
        ]);
    }
}
