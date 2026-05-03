<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'owner_name',
        'cr_number',
        'unified_number',
        'attestation_date',
        'attestation_expiry_date',
        'cr_image',
        'email',
        'phone',
        'logo',
        'address',
        'status',
        'is_active',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function managers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(CompanyManager::class, 'company_manager_company');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
