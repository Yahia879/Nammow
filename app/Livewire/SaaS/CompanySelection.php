<?php

namespace App\Livewire\SaaS;

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CompanySelection extends Component
{
    public $companies;

    public function mount()
    {
        $user = Auth::user();
        
        // Basic security check
        if (!$user || !$user->hasRole('company')) {
            return redirect('/home');
        }

        // Fetch companies through manager relationship
        $this->companies = $user->companyManager 
            ? $user->companyManager->companies 
            : collect();

        // If only one company, auto-select it
        if ($this->companies->count() === 1) {
            return $this->selectCompany($this->companies->first()->id);
        }

        // If no companies, redirect to a safe place
        if ($this->companies->count() === 0) {
             return redirect('/dashboard')->with('error', __('No companies assigned to your account.'));
        }
    }

    public function selectCompany($id)
    {
        $user = Auth::user();
        
        // Ensure user is authorized
        if (!$user || !$user->hasRole('company')) {
            return redirect('/login');
        }

        // Security check: ensure user manages this company
        $isManaged = $user->companyManager && 
                     $user->companyManager->companies()->where('companies.id', $id)->exists();

        if ($isManaged) {
            session(['active_company_id' => $id]);
            return redirect()->route('company.dashboard');
        }

        $this->dispatch('toastr', type: 'error', message: __('Access Denied'));
    }

    public function render()
    {
        return view('livewire.saa-s.company-selection')
            ->layout('livewire.layouts.app', [
                'isMenu' => false, // Hide sidebar for cleaner selection
                'contentNavbar' => true,
            ]);
    }
}
