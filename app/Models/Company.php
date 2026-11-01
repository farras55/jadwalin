<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'address',
        'timezone',
        'status',
        'office_latitude',
        'office_longitude',
        'attendance_radius_meters',
    ];

    protected function casts(): array
    {
        return [
            'office_latitude' => 'float',
            'office_longitude' => 'float',
            'attendance_radius_meters' => 'integer',
        ];
    }

    public function getAttendanceRadiusMetersAttribute($value): ?int
    {
        return $value !== null ? (int) $value : (int) config('jadwalin.attendance.default_radius_meters', 100);
    }

    public function setAttendanceRadiusMetersAttribute($value): void
    {
        $this->attributes['attendance_radius_meters'] = ($value !== null && $value !== '')
            ? (int) $value
            : (int) config('jadwalin.attendance.default_radius_meters', 100);
    }

    public function companySetting(): HasOne
    {
        return $this->hasOne(CompanySetting::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function shiftTemplates(): HasMany
    {
        return $this->hasMany(ShiftTemplate::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function shiftSwaps(): HasMany
    {
        return $this->hasMany(ShiftSwap::class);
    }

    public function openShiftClaims(): HasMany
    {
        return $this->hasMany(OpenShiftClaim::class);
    }

    public function timesheetReports(): HasMany
    {
        return $this->hasMany(TimesheetReport::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
