<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShiftAssignment>
 */
class ShiftAssignmentFactory extends Factory
{
    protected $model = ShiftAssignment::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'shift_id' => Shift::factory(),
            'user_id' => User::factory(),
            'is_overtime' => false,
            'overtime_status' => 'none',
            'clock_in_time' => null,
            'clock_out_time' => null,
            'late_minutes' => 0,
            'regular_hours' => 0,
            'overtime_hours' => 0,
            'status' => 'scheduled',
            'is_auto_clockout' => false,
            'manager_notes' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'clock_in_time' => Carbon::today()->setTime(8, 0, 0),
            'clock_out_time' => Carbon::today()->setTime(17, 0, 0),
            'regular_hours' => 8.0,
            'overtime_hours' => 1.0,
        ]);
    }

    public function autoClockedOut(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'is_auto_clockout' => true,
            'clock_in_time' => Carbon::today()->setTime(8, 0, 0),
            'clock_out_time' => Carbon::today()->setTime(17, 0, 0),
            'regular_hours' => 8.0,
        ]);
    }
}
