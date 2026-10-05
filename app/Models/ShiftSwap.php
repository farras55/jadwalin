<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSwap extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'shift_assignment_id',
        'target_shift_assignment_id',
        'swap_type',
        'requester_id',
        'target_user_id',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Swap type helpers (Dual-Mode: Transfer vs Barter/Exchange)
     */
    public function isTransfer(): bool
    {
        return $this->swap_type === 'transfer';
    }

    public function isExchange(): bool
    {
        return $this->swap_type === 'exchange';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function shiftAssignment(): BelongsTo
    {
        return $this->belongsTo(ShiftAssignment::class, 'shift_assignment_id');
    }

    public function targetShiftAssignment(): BelongsTo
    {
        return $this->belongsTo(ShiftAssignment::class, 'target_shift_assignment_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
