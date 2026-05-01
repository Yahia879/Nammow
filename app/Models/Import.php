<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Import extends Model
{
    use BelongsToCompany, CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'file_name',
        'file_size',
        'file_ext',
        'file_type',
        'status',
        'details',
        'current',
        'total',
    ];
}
