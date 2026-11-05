<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanySetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanySetting>
 */
class CompanySettingFactory extends Factory
{
    protected $model = CompanySetting::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'max_regular_hours' => 40,
            'max_overtime_hours' => 18,
            'work_hour_limit_type' => 'weekly',
            'min_rest_hours' => 11,
            'max_working_days_per_week' => 6,
            'auto_buffer_minutes' => 30,
            'grace_period_minutes' => 15,
            'is_auto_clockout' => true,
            'auto_clockout_buffer_hours' => 2,
            'office_latitude' => -6.2088,
            'office_longitude' => 106.8456,
            'attendance_radius_meters' => 100,
        ];
    }
}
