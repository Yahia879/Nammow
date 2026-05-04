<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return redirect('/super-admin/dashboard');
        }

        if ($user->hasRole('client')) {
            return redirect('/client/dashboard');
        }

        if ($user->hasRole('company')) {
            $managedCompanies = $user->companyManager ? $user->companyManager->companies : collect();

            if ($managedCompanies->count() === 1) {
                session(['active_company_id' => $managedCompanies->first()->id]);
                return redirect('/company/dashboard');
            }

            if ($managedCompanies->count() > 1) {
                return redirect()->route('select-company');
            }

            // Fallback for company managers with no companies
            return redirect('/dashboard')->with('error', __('No companies assigned to your account.'));
        }

        if ($user->hasRole('employee')) {
            return redirect('/employee/dashboard');
        }

        // Default fallback if roles aren't assigned yet or for existing roles
        return redirect('/dashboard');
    }
}
