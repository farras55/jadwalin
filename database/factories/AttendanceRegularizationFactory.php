<?php

namespace Database\Factories;

use App\Models\AttendanceRegularization;
use App\Models\Company;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRegularization>
 */
class AttendanceRegularizationFactory extends Factory
{
    protected $model = AttendanceRegularization::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'shift_assignment_id' => ShiftAssignment::factory()->autoClockedOut(),
            'employee_id' => User::factory(),
            'requested_clock_out_time' => Carbon::today()->setTime(19, 0, 0),
            'reason' => 'Lembur menyelesaikan laporan closing shift bulanan sampai malam.',
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_note' => null,
            'overtime_status' => 'none',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
            'review_note' => 'Disetujui oleh manajer.',
            'overtime_status' => 'approved',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => 'rejected',
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
            'review_note' => 'Ditolak: tidak ada konfirmasi lembur sebelumnya.',
        ]);
    }
}
