<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvanceSetting extends Model
{
    use BelongsToCompany, CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'is_enabled',
        'max_advance_type',
        'max_advance_value',
        'max_installments',
        'allow_new_advance_with_open_balance',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
