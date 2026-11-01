<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySetting extends Model
{
    use HasFactory;

    /**
     * Field yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'company_id',
        'max_regular_hours',
        'max_overtime_hours',
        'work_hour_limit_type',
        'min_rest_hours',
        'max_working_days_per_week',
        'auto_buffer_minutes',
        'grace_period_minutes',
        'is_auto_clockout',
        'auto_clockout_buffer_hours',
        'office_latitude',
        'office_longitude',
        'attendance_radius_meters',
    ];

    /**
     * Konversi tipe data atribut model.
     */
    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'max_regular_hours' => 'integer',
            'max_overtime_hours' => 'integer',
            'work_hour_limit_type' => 'string',
            'min_rest_hours' => 'integer',
            'max_working_days_per_week' => 'integer',
            'auto_buffer_minutes' => 'integer',
            'grace_period_minutes' => 'integer',
            'is_auto_clockout' => 'boolean',
            'auto_clockout_buffer_hours' => 'integer',
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

    public function getOfficeLatitudeAttribute($value): ?float
    {
        if ($value !== null) {
            return (float) $value;
        }

        $default = config('jadwalin.attendance.default_latitude');

        return $default !== null ? (float) $default : null;
    }

    public function getOfficeLongitudeAttribute($value): ?float
    {
        if ($value !== null) {
            return (float) $value;
        }

        $default = config('jadwalin.attendance.default_longitude');

        return $default !== null ? (float) $default : null;
    }

    /**
     * Relasi ke perusahaan.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
