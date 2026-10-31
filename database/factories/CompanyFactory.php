<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone_number' => fake()->numerify('08##########'),
            'address' => fake()->address(),
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ];
    }
}
