<?php

namespace App\Livewire\SaaS;

use App\Models\Company;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Companies extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    // Search and Filter properties
    public $searchTerm = '';
    public $filterStatus = '';

    // Form properties
    public $name;
    public $email;
    public $phone;
    public $address;
    public $status = 'active';
    public $logo;

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
        $this->reset(['name', 'email', 'phone', 'address', 'status', 'logo']);
        $this->status = 'active';
    }

    public function showCreateCompanyModal()
    {
        $this->resetInputs();
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
        ]);

        $this->resetInputs();
        $this->dispatch('closeModal', elementId: '#companyModal');
        $this->dispatch('toastr', type: 'success', message: __('Company created successfully!'));
    }

    public function render()
    {
        $companies = Company::where('client_id', auth()->user()->client_id)
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
