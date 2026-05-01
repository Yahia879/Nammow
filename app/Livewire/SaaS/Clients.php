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

    public $filterIsActive = '1';

    // Client Properties
    public $clientId;

    public $name;

    public $email;

    public $phone;

    public $status = 'active';

    public $plan_id;

    public $password;

    public $password_confirmation;

    public $isEdit = false;

    public $confirmedId;

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:clients,email,'.$this->clientId.'|unique:users,email,'.($this->clientId ? User::where('client_id', $this->clientId)->first()?->id : 'NULL'),
            'status' => 'required|in:active,inactive,suspended,expired',
            'phone' => 'nullable',
            'plan_id' => 'nullable|integer',
            'password' => $this->isEdit ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed',
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

    public function updatingFilterIsActive()
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
            ->when($this->filterIsActive !== '', function ($query) {
                return $query->where('is_active', $this->filterIsActive);
            })
            ->paginate(10);

        return view('livewire.saa-s.clients', [
            'clients' => $clients,
            'plans' => \App\Models\Plan::where('status', 'active')->get(),
        ]);
    }

    public function resetInputs()
    {
        $this->reset(['clientId', 'name', 'email', 'phone', 'status', 'plan_id', 'password', 'password_confirmation', 'isEdit']);
        $this->status = 'active';
    }

    public function showCreateClientModal()
    {
        $this->resetInputs();
        $this->isEdit = false;
    }

    public function editClient($id)
    {
        $this->resetInputs();
        $this->isEdit = true;
        $this->clientId = $id;

        $client = Client::find($id);
        $this->name = $client->name;
        $this->email = $client->email;
        $this->phone = $client->phone;
        $this->status = $client->status;
        $this->plan_id = $client->plan_id;
    }

    public function submit()
    {
        $this->isEdit ? $this->update() : $this->store();
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
                'is_active' => true,
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
        $this->dispatch('closeModal', elementId: '#clientModal');
        $this->dispatch('toastr', type: 'success', message: __('Client and user account created successfully!'));
    }

    public function update()
    {
        $this->validate();

        DB::transaction(function () {
            $client = Client::find($this->clientId);
            $client->update([
                'name' => $this->name,
                'slug' => Str::slug($this->name),
                'email' => $this->email,
                'phone' => $this->phone,
                'status' => $this->status,
                'plan_id' => $this->plan_id,
            ]);

            $user = User::where('client_id', $this->clientId)->first();
            if ($user) {
                $userData = [
                    'name' => $this->name,
                    'email' => $this->email,
                    'username' => $this->email,
                ];

                if ($this->password) {
                    $userData['password'] = Hash::make($this->password);
                }

                $user->update($userData);
            }
        });

        $this->resetInputs();
        $this->dispatch('closeModal', elementId: '#clientModal');
        $this->dispatch('toastr', type: 'success', message: __('Client updated successfully!'));
    }

    public function confirmDelete($id)
    {
        $this->confirmedId = $id;
    }

    public function deleteClient($id)
    {
        $client = Client::find($id);
        if ($client) {
            $client->update(['is_active' => false]);
            $this->dispatch('toastr', type: 'success', message: __('Client deactivated successfully!'));
        }
        $this->confirmedId = null;
    }
}
