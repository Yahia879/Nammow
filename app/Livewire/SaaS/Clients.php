<?php

namespace App\Livewire\SaaS;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    public $password;

    public $password_confirmation;

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:clients,email|unique:users,email',
            'status' => 'required|in:active,inactive,suspended,expired',
            'phone' => 'nullable',
            'plan_id' => 'nullable|integer',
            'password' => 'required|min:8|confirmed',
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
        $this->reset(['name', 'email', 'phone', 'status', 'plan_id', 'password', 'password_confirmation']);
        $this->status = 'active';
    }

    public function store()
    {
        $this->validate();

        DB::transaction(function () {
            $client = Client::create([
                'name' => $this->name,
                'slug' => Str::slug($this->name),
                'email' => $this->email,
                'phone' => $this->phone,
                'status' => $this->status,
                'plan_id' => $this->plan_id,
            ]);

            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'username' => $this->email,
                'password' => Hash::make($this->password),
                'client_id' => $client->id,
                'profile_photo_path' => 'profile-photos/.default-photo.jpg',
            ]);

            $user->assignRole('client');
        });

        $this->resetInputs();
        $this->dispatch('closeModal', elementId: '#createClientModal');
        $this->dispatch('toastr', type: 'success', message: __('Client and user account created successfully!'));
    }
}
