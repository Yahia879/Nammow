<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salary extends Model
{
    use BelongsToCompany, CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'company_id',
        'amount',
        'allowances',
        'tax',
        'insurance',
        'effective_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'amount' => 'float',
        'allowances' => 'float',
        'tax' => 'float',
        'insurance' => 'float',
    ];

    public function getNetSalaryAttribute()
    {
        return $this->amount + $this->allowances - $this->tax - $this->insurance;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
