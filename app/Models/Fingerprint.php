<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\CreatedUpdatedDeletedBy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fingerprint extends Model
{
    use BelongsToCompany, CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

    protected $fillable = ['company_id', 'employee_id', 'date', 'log', 'check_in', 'check_out', 'is_checked', 'excuse'];

    // 👉 Links
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // 👉 Attributes
    protected function checkIn(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => $value !== null ? Carbon::parse($value)->format('H:i') : '');
    }

    protected function checkOut(): Attribute
    {
        return Attribute::make(get: fn (?string $value) => $value !== null ? Carbon::parse($value)->format('H:i') : '');
    }

    // 👉 Scopes
    public function scopeFilteredFingerprints(
        Builder $query,
        $selectedEmployeeId,
        $fromDate,
        $toDate,
        $isAbsence,
        $isOneFingerprint
    ): void {
        $query
            ->where('employee_id', $selectedEmployeeId)
            ->whereBetween('date', [$fromDate, $toDate])
            ->when($isAbsence, function ($query) {
                return $query->whereNull('log');
            })
            ->when($isOneFingerprint, function ($query) {
                return $query->whereNotNull('check_in')->whereNull('check_out');
            })
            ->orderBy('date');
    }
}
