<?php

namespace App\Livewire\SaaS;

use App\Models\Client;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Clients extends Component
{
    use WithPagination;

    public $searchTerm = '';

    public $filterStatus = '';

    // Create Client Properties
    public $name;

    public $email;

    public $phone;

    public $status = 'active';

    public $plan_id;

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:clients,email',
            'status' => 'required|in:active,inactive,suspended,expired',
            'phone' => 'nullable',
            'plan_id' => 'nullable|integer',
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

    public function render()
    {
        $clients = Client::with(['plan'])->withCount('companies')
            ->where(function ($query) {
                $query->where('name', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('email', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('slug', 'like', '%'.$this->searchTerm.'%')
                    ->orWhere('phone', 'like', '%'.$this->searchTerm.'%');
            })
            ->when($this->filterStatus, function ($query) {
                return $query->where('status', $this->filterStatus);
            })
            ->paginate(10);

        return view('livewire.saa-s.clients', [
            'clients' => $clients,
            'plans' => \App\Models\Plan::where('status', 'active')->get(),
        ]);
    }

    public function resetInputs()
    {
        $this->reset(['name', 'email', 'phone', 'status', 'plan_id']);
        $this->status = 'active';
    }

    public function store()
    {
        $this->validate();

        Client::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'plan_id' => $this->plan_id,
        ]);

        $this->resetInputs();
        $this->dispatch('closeModal', elementId: '#createClientModal');
        $this->dispatch('toastr', type: 'success', message: __('Client created successfully!'));
    }
}
