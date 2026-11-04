<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Department;
use App\Models\ShiftTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShiftTemplate>
 */
class ShiftTemplateFactory extends Factory
{
    protected $model = ShiftTemplate::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'department_id' => Department::factory(),
            'title' => fake()->randomElement(['Shift Pagi', 'Shift Siang', 'Shift Malam']),
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'default_quota' => fake()->numberBetween(2, 5),
            'color_code' => fake()->hexColor(),
        ];
    }
}
