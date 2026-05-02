<?php

namespace App\Traits;

use App\Models\Scopes\CompanyScope;
use Illuminate\Support\Facades\Auth;

trait BelongsToCompany
{
    public static function bootBelongsToCompany()
    {
        static::addGlobalScope(new CompanyScope());

        static::creating(function ($model) {
            if (Auth::check()) {
                $user = Auth::user();
                $role = $user->role->name ?? null;

                if ($role === 'company') {
                    if (!$model->company_id) {
                        $model->company_id = $user->companyManager->company_id ?? $user->company_id;
                    }
                }
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
