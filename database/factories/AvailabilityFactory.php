<?php

namespace Database\Factories;

use App\Models\Availability;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Availability>
 */
class AvailabilityFactory extends Factory
{
    protected $model = Availability::class;

    public function definition(): array
    {
        $start = Carbon::tomorrow()->setTime(9, 0, 0);
        $end = Carbon::tomorrow()->setTime(17, 0, 0);

        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'is_recurring' => false,
            'day_of_week' => null,
            'start_time' => $start,
            'end_time' => $end,
            'reason' => 'Keperluan keluarga / izin mendesak',
            'status' => 'pending_manager',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => 'rejected',
            'approved_by' => User::factory(),
            'approved_at' => now(),
            'rejection_reason' => 'Jadwal kuota shift minim.',
        ]);
    }
}
