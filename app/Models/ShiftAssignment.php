<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'company_id',
        'shift_id',
        'user_id',
        'is_overtime',
        'overtime_status',
        'clock_in_time',
        'clock_out_time',
        'late_minutes',
        'regular_hours',
        'overtime_hours',
        'status',
        'is_auto_clockout',
        'manager_notes',
    ];

    protected function casts(): array
    {
        return [
            'is_overtime' => 'boolean',
            'clock_in_time' => 'datetime',
            'clock_out_time' => 'datetime',
            'late_minutes' => 'integer',
            'regular_hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'is_auto_clockout' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shiftSwaps(): HasMany
    {
        return $this->hasMany(ShiftSwap::class, 'shift_assignment_id');
    }
}
