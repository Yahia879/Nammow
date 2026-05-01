<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use BelongsToCompany, CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'company_id',
        'old_id',
        'serial_number',
        'status',
        'description',
        'in_service',
        'real_price',
        'expected_price',
        'acquisition_date',
        'acquisition_type',
        'funded_by',
        'note',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    // 👉 Links
    public function transitions(): HasMany
    {
        return $this->hasMany(Transition::class);
    }
}
