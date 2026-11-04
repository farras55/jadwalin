<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Department;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shift>
 */
class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition(): array
    {
        $start = Carbon::today()->setTime(8, 0, 0);
        $end = Carbon::today()->setTime(16, 0, 0);

        return [
            'company_id' => Company::factory(),
            'department_id' => Department::factory(),
            'template_id' => null,
            'title' => 'Shift Reguler Pagi',
            'start_time' => $start,
            'end_time' => $end,
            'quota' => fake()->numberBetween(2, 5),
            'is_open_shift' => false,
            'is_global_broadcast' => false,
            'requires_approval' => true,
            'is_holiday' => false,
            'status' => 'published',
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => 'published']);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function openShift(): static
    {
        return $this->state(fn () => [
            'is_open_shift' => true,
            'status' => 'published',
        ]);
    }
}
