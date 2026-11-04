<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement(['Operasional', 'IT Support', 'Finance', 'Kitchen', 'Service & Bar', 'Marketing']),
            'description' => fake()->sentence(),
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius' => 100,
            'radius_meters' => 100,
            'address' => fake()->address(),
        ];
    }
}
