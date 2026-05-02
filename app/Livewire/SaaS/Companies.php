<?php

namespace App\Livewire\SaaS;

use App\Models\Company;
use App\Models\User;
use App\Models\CompanyManager;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Companies extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    // Search and Filter properties
    public $searchTerm = '';
    public $filterStatus = 'active';

    // Form properties
    public $companyId;
    public $name;
    public $email;
    public $phone;
    public $address;
    public $status = 'active';
    public $logo;
    public $existingLogo;

    // Multi-Manager properties
    public $managers = [];
    public $activeTab = 'company-info';

    public $isEdit = false;
    public $confirmedId;

    public function mount()
    {
        $this->addManager();
    }

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|min:3',
            'email' => 'nullable|email',
            'phone' => 'nullable',
            'address' => 'nullable',
            'status' => 'required|in:active,inactive',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];

        foreach ($this->managers as $index => $manager) {
            if (!isset($manager['is_existing']) || !$manager['is_existing']) {
                $rules["managers.{$index}.name"] = 'required|string|min:3';
                $rules["managers.{$index}.email"] = 'required|email|unique:users,email|distinct';
                $rules["managers.{$index}.phone"] = 'nullable';
                $rules["managers.{$index}.password"] = 'required|min:8|confirmed';
            }
        }

        return $rules;
    }

    protected function validationAttributes()
    {
        $attributes = [];
        foreach ($this->managers as $index => $manager) {
            $attributes["managers.{$index}.name"] = __('Manager Name') . ' (#' . ($index + 1) . ')';
            $attributes["managers.{$index}.email"] = __('Manager Email') . ' (#' . ($index + 1) . ')';
            $attributes["managers.{$index}.phone"] = __('Manager Phone') . ' (#' . ($index + 1) . ')';
            $attributes["managers.{$index}.password"] = __('Manager Password') . ' (#' . ($index + 1) . ')';
        }
        return $attributes;
    }

    public function addManager()
    {
        $this->managers[] = [
            'name' => '',
            'email' => '',
            'phone' => '',
            'password' => '',
            'password_confirmation' => '',
        ];
    }

    public function removeManager($index)
    {
        unset($this->managers[$index]);
        $this->managers = array_values($this->managers); // Re-index
        if (count($this->managers) === 0) {
            $this->addManager();
        }
    }

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function resetInputs()
    {
        $this->reset(['companyId', 'name', 'email', 'phone', 'address', 'status', 'logo', 'existingLogo', 'isEdit', 'managers', 'activeTab']);
        $this->status = 'active';
        $this->activeTab = 'company-info';
        $this->addManager();
    }

    public function showCreateCompanyModal()
    {
        $this->resetInputs();
        $this->isEdit = false;
    }

    public function editCompany($id)
    {
        $this->resetInputs();
        $this->isEdit = true;
        $this->companyId = $id;

        $company = Company::where('client_id', auth()->user()->client_id)->with('managers.user')->findOrFail($id);
        
        $this->name = $company->name;
        $this->email = $company->email;
        $this->phone = $company->phone;
        $this->address = $company->address;
        $this->status = $company->status;
        $this->existingLogo = $company->logo;

        // Load existing managers
        $this->managers = [];
        foreach ($company->managers as $manager) {
            $this->managers[] = [
                'id' => $manager->id,
                'name' => $manager->user->name ?? '',
                'email' => $manager->user->email ?? '',
                'phone' => $manager->user->mobile ?? '',
                'status' => $manager->status,
                'is_existing' => true,
            ];
        }

        if (empty($this->managers)) {
            $this->addManager();
        }
    }

    public function submit()
    {
        $this->isEdit ? $this->update() : $this->store();
    }

    public function store()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $logoPath = null;
            if ($this->logo) {
                $logoPath = $this->logo->store('company-logos', 'public');
            }

            $company = Company::create([
                'client_id' => auth()->user()->client_id,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'status' => $this->status,
                'logo' => $logoPath,
                'is_active' => true,
            ]);

            foreach ($this->managers as $managerData) {
                $user = User::create([
                    'name' => $managerData['name'],
                    'email' => $managerData['email'],
                    'mobile' => $managerData['phone'] ?: null,
                    'password' => Hash::make($managerData['password']),
                    'client_id' => auth()->user()->client_id,
                    'company_id' => $company->id,
                ]);

                // Assign role
                $user->assignRole('company');

                CompanyManager::create([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'status' => 'active',
                ]);
            }

            DB::commit();

            $this->resetInputs();
            $this->dispatch('closeModal', elementId: '#companyModal');
            $this->dispatch('toastr', type: 'success', message: __('Company and managers created successfully!'));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toastr', type: 'error', message: __('Error: ') . $e->getMessage());
        }
    }

    public function update()
    {
        $this->validate();

        $company = Company::where('client_id', auth()->user()->client_id)->findOrFail($this->companyId);

        DB::beginTransaction();
        try {
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'status' => $this->status,
            ];

            if ($this->logo) {
                // Delete old logo if it exists
                if ($company->logo) {
                    Storage::disk('public')->delete($company->logo);
                }
                $data['logo'] = $this->logo->store('company-logos', 'public');
            }

            $company->update($data);

            // Handle new managers added during edit
            foreach ($this->managers as $managerData) {
                if (!isset($managerData['is_existing']) || !$managerData['is_existing']) {
                    $user = User::create([
                        'name' => $managerData['name'],
                        'email' => $managerData['email'],
                        'mobile' => $managerData['phone'] ?: null,
                        'password' => Hash::make($managerData['password']),
                        'client_id' => auth()->user()->client_id,
                        'company_id' => $company->id,
                    ]);

                    // Assign role
                    $user->assignRole('company');

                    CompanyManager::create([
                        'company_id' => $company->id,
                        'user_id' => $user->id,
                        'status' => 'active',
                    ]);
                }
            }

            DB::commit();

            $this->resetInputs();
            $this->dispatch('closeModal', elementId: '#companyModal');
            $this->dispatch('toastr', type: 'success', message: __('Company updated successfully!'));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toastr', type: 'error', message: __('Error: ') . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->confirmedId = $id;
    }

    public function deleteCompany($id)
    {
        $company = Company::where('client_id', auth()->user()->client_id)->find($id);
        if ($company) {
            $company->update(['is_active' => false]);
            $this->dispatch('toastr', type: 'success', message: __('Company deactivated successfully!'));
        }
        $this->confirmedId = null;
    }

    public function render()
    {
        $companies = Company::where('client_id', auth()->user()->client_id)
            ->where('is_active', true)
            ->withCount('managers')
            ->where(function ($query) {
                $query->where('name', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('email', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('phone', 'like', '%'.$this->searchTerm.'%');
            })
            ->when($this->filterStatus, function ($query) {
                return $query->where('status', $this->filterStatus);
            })
            ->paginate(10);

        return view('livewire.saa-s.companies', [
            'companies' => $companies,
        ]);
    }
}
