<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use CreatedUpdatedDeletedBy,
        HasApiTokens,
        HasFactory,
        HasProfilePhoto,
        HasRoles,
        Notifiable,
        SoftDeletes,
        TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'employee_id',
        'mobile',
        'mobile_verified_at',
        'username',
        'email',
        'email_verified_at',
        'password',
        'profile_photo_path',
        'client_id',
        'company_id',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['profile_photo_url'];

    // 👉 Mutators
    public function setMobileAttribute($value)
    {
        $this->attributes['mobile'] = $value ?: null;
    }

    // 👉 Links
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function companyManager(): HasOne
    {
        return $this->hasOne(CompanyManager::class);
    }

    // 👉 Attributes
    public function getEmployeeFullNameAttribute()
    {
        if ($this->employee) {
            return $this->employee->first_name.' '.$this->employee->last_name;
        }

        return '';
    }
}
