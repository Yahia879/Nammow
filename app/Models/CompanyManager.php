<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'status',
    ];

    public function companies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_manager_company');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
