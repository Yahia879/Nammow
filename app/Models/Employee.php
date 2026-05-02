<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use BelongsToCompany, CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'company_id',
        'contract_id',
        'first_name',
        'father_name',
        'last_name',
        'mother_name',
        'birth_and_place',
        'national_number',
        'mobile_number',
        'degree',
        'gender',
        'address',
        'notes',
        'balance_leave_allowed',
        'max_leave_allowed',
        'delay_counter',
        'hourly_counter',
        'is_active',
        'quit_date',
    ];

    // 👉 Links
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function fingerprints(): HasMany
    {
        return $this->hasMany(Fingerprint::class);
    }

    public function leaves(): BelongsToMany
    {
        return $this->belongsToMany(Leave::class)
            ->withPivot(
                'id',
                'from_date',
                'to_date',
                'start_at',
                'end_at',
                'note',
                'is_authorized',
                'is_checked',
                'created_by',
                'updated_by',
                'deleted_by',
                'created_at',
                'updated_at',
                'deleted_at'
            )
            ->using(EmployeeLeave::class);
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(Timeline::class);
    }

    public function activeTimeline(): HasOne
    {
        return $this->hasOne(Timeline::class)->whereNull('end_date');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    public function salary(): HasOne
    {
        return $this->hasOne(Salary::class)->latestOfMany();
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    // 👉 Attributes
    public function getFullNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    protected function birthAndPlace(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => ucfirst($value)
        );
    }

    public function getWorkedYearsAttribute()
    {
        $lastIsSequentRange = Timeline::where('employee_id', $this->id)
            ->where('is_sequent', 0)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastIsSequentRange) {
            $startDateRow = Timeline::where('is_sequent', 1)
                ->where('employee_id', $this->id)
                ->where('id', '>', $lastIsSequentRange->id)
                ->orderBy('start_date')
                ->first();
        } else {
            $startDateRow = Timeline::where('is_sequent', 1)
                ->where('employee_id', $this->id)
                ->orderBy('start_date')
                ->first();
        }

        if (! $startDateRow) {
            $startDateRow = Timeline::where('employee_id', $this->id)
                ->latest()
                ->first();
        }

        $startDate = optional($startDateRow)->start_date;

        $workedYear = $startDate ? Carbon::now()->year - Carbon::parse($startDate)->year : 0;

        return $workedYear ?: 1;
    }

    public function getCurrentPositionAttribute()
    {
        $data = Timeline::with('position')
            ->where('employee_id', $this->id)
            ->whereNull('end_date')
            ->first();
        if ($data) {
            return $data->position->name;
        } else {
            return '---';
        }
    }

    public function getCurrentDepartmentAttribute()
    {
        $data = Timeline::with('department')
            ->where('employee_id', $this->id)
            ->whereNull('end_date')
            ->first();
        if ($data) {
            return $data->department->name;
        } else {
            return '---';
        }
    }

    public function getCurrentCenterAttribute()
    {
        $data = Timeline::with('center')
            ->where('employee_id', $this->id)
            ->whereNull('end_date')
            ->first();
        if ($data) {
            return $data->center->name;
        } else {
            return '---';
        }
    }

    public function getJoinAtShortFormAttribute()
    {
        $data = Timeline::where('employee_id', $this->id)->first();
        if ($data) {
            return __('Joined').' '.Carbon::parse($data->start_date)->diffForHumans();
        } else {
            return '---';
        }
    }

    public function getJoinAtAttribute()
    {
        $data = Timeline::where('employee_id', $this->id)->first();
        if ($data) {
            return Carbon::parse($data->start_date)->format('j F Y');
        } else {
            return '---';
        }
    }

    public function getEmployeePhoto()
    {
        $defaultPhotoName = 'profile-photos/.default-photo.jpg';
        $user = User::where('employee_id', $this->id)->first();

        if ($user) {
            return 'storage/'.$user->profile_photo_path;
        }

        return 'storage/'.$defaultPhotoName;
    }

    // 👉 Functions
    public static function search($searchTerm)
    {
        return empty($searchTerm)
            ? static::query()
            : static::query()
                ->where('id', 'like', '%'.$searchTerm.'%')
                ->orWhere('first_name', 'like', '%'.$searchTerm.'%')
                ->orWhere('father_name', 'like', '%'.$searchTerm.'%')
                ->orWhere('last_name', 'like', '%'.$searchTerm.'%')
                ->orWhere('national_number', 'like', '%'.$searchTerm.'%');
    }
}
