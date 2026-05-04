<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->hasRole('company')) {
            $managedCompanies = $user->companyManager ? $user->companyManager->companies : collect();
            $managedCompanyIds = $managedCompanies->pluck('id')->toArray();

            // If only one company, set it automatically and move on
            if ($managedCompanies->count() === 1) {
                session(['active_company_id' => $managedCompanies->first()->id]);
                return $next($request);
            }

            // Security check: If multiple companies and (none selected OR selected is not managed by user)
            if ($managedCompanies->count() > 1) {
                if (!session()->has('active_company_id') || !in_array(session('active_company_id'), $managedCompanyIds)) {
                    // Avoid infinite redirect
                    if (!$request->is('select-company*') && !$request->is('logout')) {
                        return redirect()->route('select-company');
                    }
                }
            }

            // If no companies assigned
            if ($managedCompanies->count() === 0) {
                if (!$request->is('logout')) {
                    Auth::logout();
                    return redirect()->route('login')->with('error', __('No companies assigned to your account.'));
                }
            }
        }

        return $next($request);
    }
}
