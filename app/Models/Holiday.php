<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'company_manager_id',
        'created_by_type',
        'title',
        'description',
        'start_date',
        'end_date',
        'scope',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function companyManager()
    {
        return $this->belongsTo(User::class, 'company_manager_id');
    }

    public function holidayCompanies()
    {
        return $this->hasMany(HolidayCompany::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'holiday_companies');
    }
}
