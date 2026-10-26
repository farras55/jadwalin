<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'latitude',
        'longitude',
        'address',
        'radius_meters',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_meters' => 'integer',
        ];
    }

    public function getLatitudeAttribute($value): float
    {
        return $value !== null ? (float) $value : -6.2383200;
    }

    public function getLongitudeAttribute($value): float
    {
        return $value !== null ? (float) $value : 106.8122800;
    }

    public function getRadiusMetersAttribute($value): int
    {
        return $value ? (int) $value : 50;
    }

    public function getAddressAttribute($value): string
    {
        return $value ?: 'Jl. Suryo No. 12, Senopati, Kebayoran Baru';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
}

