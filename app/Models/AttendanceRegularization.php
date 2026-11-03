<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRegularization extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'shift_assignment_id',
        'employee_id',
        'requested_clock_out_time',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
        'overtime_status',
    ];

    protected function casts(): array
    {
        return [
            'requested_clock_out_time' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function shiftAssignment(): BelongsTo
    {
        return $this->belongsTo(ShiftAssignment::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
