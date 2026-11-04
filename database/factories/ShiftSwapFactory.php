<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ShiftAssignment;
use App\Models\ShiftSwap;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShiftSwap>
 */
class ShiftSwapFactory extends Factory
{
    protected $model = ShiftSwap::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'shift_assignment_id' => ShiftAssignment::factory(),
            'target_shift_assignment_id' => null,
            'swap_type' => 'transfer',
            'requester_id' => User::factory(),
            'target_user_id' => User::factory(),
            'reason' => 'Ada jadwal perkuliahan pengganti.',
            'status' => 'pending_target',
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    public function exchange(): static
    {
        return $this->state(fn () => [
            'swap_type' => 'exchange',
            'target_shift_assignment_id' => ShiftAssignment::factory(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }
}
