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
    public $owner_name;
    public $cr_number;
    public $unified_number;
    public $attestation_date;
    public $attestation_expiry_date;
    public $email;
    public $phone;
    public $address;
    public $status = 'active';
    public $cr_image;
    public $existingCrImage;

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
            'owner_name' => 'required|string|min:3',
            'cr_number' => 'required|string',
            'unified_number' => 'required|string',
            'attestation_date' => 'required|date',
            'attestation_expiry_date' => 'required|date',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'nullable',
            'cr_image' => ($this->isEdit ? 'nullable' : 'required') . '|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ];

        foreach ($this->managers as $index => $manager) {
            if ($this->isEdit && (!isset($manager['is_existing']) || !$manager['is_existing'])) {
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
        if ($this->isEdit) {
            foreach ($this->managers as $index => $manager) {
                $attributes["managers.{$index}.name"] = __('Manager Name') . ' (#' . ($index + 1) . ')';
                $attributes["managers.{$index}.email"] = __('Manager Email') . ' (#' . ($index + 1) . ')';
                $attributes["managers.{$index}.phone"] = __('Manager Phone') . ' (#' . ($index + 1) . ')';
                $attributes["managers.{$index}.password"] = __('Manager Password') . ' (#' . ($index + 1) . ')';
            }
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
        if (count($this->managers) === 0 && $this->isEdit) {
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
        $this->reset(['companyId', 'name', 'owner_name', 'cr_number', 'unified_number', 'attestation_date', 'attestation_expiry_date', 'email', 'phone', 'address', 'status', 'cr_image', 'existingCrImage', 'isEdit', 'managers', 'activeTab']);
        $this->status = 'active';
        $this->activeTab = 'company-info';
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
        $this->owner_name = $company->owner_name;
        $this->cr_number = $company->cr_number;
        $this->unified_number = $company->unified_number;
        $this->attestation_date = $company->attestation_date;
        $this->attestation_expiry_date = $company->attestation_expiry_date;
        $this->email = $company->email;
        $this->phone = $company->phone;
        $this->address = $company->address;
        $this->status = $company->status;
        $this->existingCrImage = $company->cr_image;

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
            $crImagePath = null;
            if ($this->cr_image) {
                $crImagePath = $this->cr_image->store('company-cr-images', 'public');
            }

            $company = Company::create([
                'client_id' => auth()->user()->client_id,
                'name' => $this->name,
                'owner_name' => $this->owner_name,
                'cr_number' => $this->cr_number,
                'unified_number' => $this->unified_number,
                'attestation_date' => $this->attestation_date,
                'attestation_expiry_date' => $this->attestation_expiry_date,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'status' => $this->status,
                'cr_image' => $crImagePath,
                'is_active' => true,
            ]);

            DB::commit();

            $this->resetInputs();
            $this->dispatch('closeModal', elementId: '#companyModal');
            $this->dispatch('toastr', type: 'success', message: __('Company created successfully!'));
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
                'owner_name' => $this->owner_name,
                'cr_number' => $this->cr_number,
                'unified_number' => $this->unified_number,
                'attestation_date' => $this->attestation_date,
                'attestation_expiry_date' => $this->attestation_expiry_date,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'status' => $this->status,
            ];

            if ($this->cr_image) {
                // Delete old image if it exists
                if ($company->cr_image) {
                    Storage::disk('public')->delete($company->cr_image);
                }
                $data['cr_image'] = $this->cr_image->store('company-cr-images', 'public');
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
