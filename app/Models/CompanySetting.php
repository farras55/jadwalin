<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySetting extends Model
{
    use HasFactory;

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
    ];

    protected function casts(): array
    {
        return [
            'max_regular_hours' => 'integer',
            'max_overtime_hours' => 'integer',
            'min_rest_hours' => 'integer',
            'max_working_days_per_week' => 'integer',
            'auto_buffer_minutes' => 'integer',
            'grace_period_minutes' => 'integer',
            'is_auto_clockout' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
