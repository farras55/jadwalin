<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'company_id',
        'department_id',
        'position_id',
        'is_global_manager',
        'name',
        'email',
        'password',
        'role',
        'employment_type',
        'max_weekly_hours',
        'phone_number',
        'join_date',
        'status',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_global_manager' => 'boolean',
            'join_date' => 'date',
            'max_weekly_hours' => 'integer',
        ];
    }

    /**
     * Role helper methods
     */
    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    /**
     * Employment type helper methods
     */
    public function isFullTime(): bool
    {
        return $this->employment_type === 'full_time';
    }

    public function isPartTime(): bool
    {
        return $this->employment_type === 'part_time';
    }

    public function isFreelance(): bool
    {
        return $this->employment_type === 'freelance';
    }

    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    public function initiatedSwaps(): HasMany
    {
        return $this->hasMany(ShiftSwap::class, 'requester_id');
    }

    public function receivedSwaps(): HasMany
    {
        return $this->hasMany(ShiftSwap::class, 'target_user_id');
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

