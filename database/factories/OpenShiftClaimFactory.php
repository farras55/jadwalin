<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\OpenShiftClaim;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OpenShiftClaim>
 */
class OpenShiftClaimFactory extends Factory
{
    protected $model = OpenShiftClaim::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'shift_id' => Shift::factory()->openShift(),
            'user_id' => User::factory(),
            'status' => 'pending_manager',
            'applied_at' => now(),
            'approved_by' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'approved_by' => User::factory(),
        ]);
    }
}
