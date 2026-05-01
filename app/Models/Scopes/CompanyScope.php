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
            $role = $user->getRoleNames()->first();

            if ($role === 'company') {
                $companyId = $user->company_id;
                if ($companyId) {
                    $builder->where($model->getTable() . '.company_id', $companyId);
                }
            }
        }
    }
}
