<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $role = $user->role->name ?? null;

            if ($role === 'company') {
                // Check for active session company first
                if (session()->has('active_company_id')) {
                    $builder->where($model->getTable() . '.company_id', session('active_company_id'));
                    return;
                }

                // Fallback to all managed companies
                $companyIds = $user->companyManager ? $user->companyManager->companies->pluck('id')->toArray() : [$user->company_id];
                if (!empty($companyIds)) {
                    $builder->whereIn($model->getTable() . '.company_id', $companyIds);
                }
            }
        }
    }
}
