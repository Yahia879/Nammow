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
        'role_id',
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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function actions()
    {
        return $this->belongsToMany(Action::class);
    }

    public function canAction($actionName)
    {
        $actions = is_array($actionName) ? $actionName : [$actionName];

        // Check direct user actions
        if ($this->actions()->whereIn('name', $actions)->exists()) {
            return true;
        }

        // Check role actions
        if ($this->role && $this->role->actions()->whereIn('name', $actions)->exists()) {
            return true;
        }

        return false;
    }

    public function hasRole($role)
    {
        return $this->role && $this->role->name === $role;
    }

    public function hasAnyRole($roles)
    {
        if (!is_array($roles)) {
            $roles = explode('|', $roles);
        }
        return $this->role && in_array($this->role->name, $roles);
    }
}
