<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    // Standard Department Names / Types Constants
    public const OPERASIONAL = 'Operasional';

    public const PRODUKSI = 'Produksi';

    public const ADMINISTRASI = 'Administrasi';

    public const FINANCE = 'Finance';

    public const HR = 'HR';

    public const IT = 'IT';

    public const MARKETING = 'Marketing';

    public const RD = 'R&D';

    public const SALES = 'Sales';

    public const FOOD_AND_BEVERAGE = 'Food & Beverage';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'latitude',
        'longitude',
        'address',
        'radius',
        'radius_meters',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'radius' => 'integer',
            'radius_meters' => 'integer',
        ];
    }

    public function getLatitudeAttribute($value): ?float
    {
        if ($value !== null) {
            return (float) $value;
        }

        if ($this->company?->office_latitude !== null) {
            return (float) $this->company->office_latitude;
        }

        $defaultLat = config('jadwalin.attendance.default_latitude');

        return $defaultLat !== null ? (float) $defaultLat : null;
    }

    public function getLongitudeAttribute($value): ?float
    {
        if ($value !== null) {
            return (float) $value;
        }

        if ($this->company?->office_longitude !== null) {
            return (float) $this->company->office_longitude;
        }

        $defaultLng = config('jadwalin.attendance.default_longitude');

        return $defaultLng !== null ? (float) $defaultLng : null;
    }

    public function getRadiusAttribute($value): ?int
    {
        if ($value !== null) {
            return (int) $value;
        }

        $radiusMeters = $this->attributes['radius_meters'] ?? null;
        if ($radiusMeters !== null) {
            return (int) $radiusMeters;
        }

        if ($this->company?->attendance_radius_meters !== null) {
            return (int) $this->company->attendance_radius_meters;
        }

        return (int) config('jadwalin.attendance.default_radius_meters', 100);
    }

    public function setRadiusAttribute($value): void
    {
        $this->attributes['radius'] = (int) $value;
        $this->attributes['radius_meters'] = (int) $value;
    }

    public function getRadiusMetersAttribute($value): ?int
    {
        return $this->getRadiusAttribute($value);
    }

    public function setRadiusMetersAttribute($value): void
    {
        $this->attributes['radius_meters'] = (int) $value;
        $this->attributes['radius'] = (int) $value;
    }

    public function getAddressAttribute($value): ?string
    {
        if (! empty($value)) {
            return (string) $value;
        }

        return $this->company?->address ?: config('jadwalin.attendance.default_address');
    }

    /**
     * Memeriksa apakah koordinat lokasi cabang sudah dikonfigurasi.
     */
    public function isLocationConfigured(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
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
