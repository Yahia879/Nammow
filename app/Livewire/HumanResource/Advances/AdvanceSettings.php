<?php

namespace App\Livewire\HumanResource\Advances;

use App\Models\AdvanceSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AdvanceSettings extends Component
{
    public $selected_company_id;
    public $is_enabled;
    public $max_advance_type;
    public $max_advance_value;
    public $max_installments;
    public $allow_new_advance_with_open_balance;

    public function mount()
    {
        if (!Auth::user()->canAction('manage_advance_settings')) {
            abort(403);
        }

        $user = Auth::user();
        $this->selected_company_id = $user->company_id;

        if (!$this->selected_company_id && ($user->hasRole('super_admin') || $user->hasRole('client'))) {
            // Default to first available company if no company_id (super_admin case)
            $firstCompany = $this->getAvailableCompanies()->first();
            $this->selected_company_id = $firstCompany ? $firstCompany->id : null;
        }

        $this->loadSettings();
    }

    public function updatedSelectedCompanyId()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        if (!$this->selected_company_id) return;

        $settings = AdvanceSetting::firstOrCreate(
            ['company_id' => $this->selected_company_id],
            [
                'is_enabled' => true,
                'max_advance_type' => 'fixed',
                'max_advance_value' => 0,
                'max_installments' => 12,
                'allow_new_advance_with_open_balance' => false,
            ]
        );

        $this->is_enabled = $settings->is_enabled;
        $this->max_advance_type = $settings->max_advance_type;
        $this->max_advance_value = $settings->max_advance_value;
        $this->max_installments = $settings->max_installments;
        $this->allow_new_advance_with_open_balance = $settings->allow_new_advance_with_open_balance;
    }

    public function getAvailableCompanies()
    {
        $user = Auth::user();
        if ($user->hasRole('super_admin')) {
            return \App\Models\Company::all();
        } elseif ($user->hasRole('client')) {
            return \App\Models\Company::where('client_id', $user->client_id)->get();
        }
        return \App\Models\Company::where('id', $user->company_id)->get();
    }

    public function save()
    {
        if (!Auth::user()->canAction('manage_advance_settings')) {
            abort(403);
        }

        if (!$this->selected_company_id) {
            $this->dispatch('toastr', type: 'error', message: __('Please select a company.'));
            return;
        }

        $this->validate([
            'max_advance_type' => 'required|in:fixed,percentage',
            'max_advance_value' => 'required|numeric|min:0',
            'max_installments' => 'required|integer|min:1',
        ]);

        $settings = AdvanceSetting::where('company_id', $this->selected_company_id)->first();
        $settings->update([
            'is_enabled' => $this->is_enabled,
            'max_advance_type' => $this->max_advance_type,
            'max_advance_value' => $this->max_advance_value,
            'max_installments' => $this->max_installments,
            'allow_new_advance_with_open_balance' => $this->allow_new_advance_with_open_balance,
        ]);

        $this->dispatch('toastr', type: 'success', message: __('Settings updated successfully.'));
    }

    public function render()
    {
        return view('livewire.human-resource.advances.advance-settings', [
            'companies' => $this->getAvailableCompanies()
        ]);
    }
}
