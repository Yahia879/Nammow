<?php

namespace App\Livewire\SaaS;

use App\Models\Company;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

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

    public $isEdit = false;
    public $confirmedId;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3',
            'email' => 'nullable|email',
            'phone' => 'nullable',
            'address' => 'nullable',
            'status' => 'required|in:active,inactive',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
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
        $this->reset(['companyId', 'name', 'email', 'phone', 'address', 'status', 'logo', 'existingLogo', 'isEdit']);
        $this->status = 'active';
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

        $company = Company::where('client_id', auth()->user()->client_id)->findOrFail($id);
        
        $this->name = $company->name;
        $this->email = $company->email;
        $this->phone = $company->phone;
        $this->address = $company->address;
        $this->status = $company->status;
        $this->existingLogo = $company->logo;
    }

    public function submit()
    {
        $this->isEdit ? $this->update() : $this->store();
    }

    public function store()
    {
        $this->validate();

        $logoPath = null;
        if ($this->logo) {
            $logoPath = $this->logo->store('company-logos', 'public');
        }

        Company::create([
            'client_id' => auth()->user()->client_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => $this->status,
            'logo' => $logoPath,
            'is_active' => true,
        ]);

        $this->resetInputs();
        $this->dispatch('closeModal', elementId: '#companyModal');
        $this->dispatch('toastr', type: 'success', message: __('Company created successfully!'));
    }

    public function update()
    {
        $this->validate();

        $company = Company::where('client_id', auth()->user()->client_id)->findOrFail($this->companyId);

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

        $this->resetInputs();
        $this->dispatch('closeModal', elementId: '#companyModal');
        $this->dispatch('toastr', type: 'success', message: __('Company updated successfully!'));
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
