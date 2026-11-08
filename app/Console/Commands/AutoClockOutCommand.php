<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\CompanySetting;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoClockOutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jadwalin:auto-clockout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically clock out overdue shifts after company buffer period.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting auto clock-out process...');

        $processedCount = 0;
        $skippedCount = 0;

        ShiftAssignment::query()
            ->with(['shift', 'company.companySetting', 'user'])
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->where('is_auto_clockout', false)
            ->chunkById(100, function ($assignments) use (&$processedCount, &$skippedCount) {
                foreach ($assignments as $assignment) {
                    $shift = $assignment->shift;

                    if (! $shift) {
                        $skippedCount++;

                        continue;
                    }

                    $company = $assignment->company ?? $shift->company;
                    $timezone = $company?->timezone ?? 'Asia/Jakarta';
                    $setting = $company?->companySetting ?? CompanySetting::where('company_id', $assignment->company_id)->first();

                    // Jika perusahaan secara eksplisit menonaktifkan fitur auto clock-out
                    if ($setting && $setting->is_auto_clockout === false) {
                        $skippedCount++;

                        continue;
                    }

                    $bufferHours = ($setting && $setting->auto_clockout_buffer_hours !== null)
                        ? (int) $setting->auto_clockout_buffer_hours
                        : 1;

                    $shiftEndDateTime = self::resolveShiftEndDateTime($shift, $timezone);
                    $cutoffDateTime = $shiftEndDateTime->copy()->addHours($bufferHours);
                    $now = Carbon::now($timezone);

                    // Hanya proses jika waktu sekarang melewati batas end_time + buffer_hours
                    if ($now->greaterThan($cutoffDateTime)) {
                        $this->closeAssignment($assignment, $shiftEndDateTime, $setting, $timezone);
                        $processedCount++;
                    } else {
                        $skippedCount++;
                    }
                }
            });

        $this->info("Auto clock-out completed. Processed: {$processedCount}, Skipped: {$skippedCount}.");

        return self::SUCCESS;
    }

    /**
     * Menutup record shift_assignment tepat pada end_time dan menulis audit log.
     */
    protected function closeAssignment(ShiftAssignment $assignment, Carbon $shiftEndDateTime, ?CompanySetting $setting, string $timezone): void
    {
        DB::transaction(function () use ($assignment, $shiftEndDateTime, $setting, $timezone) {
            $clockInTime = Carbon::parse($assignment->clock_in_time, $timezone);

            // Perhitungan jam kerja hanya dihitung dari clock_in sampai batas akhir shift (shift_end_datetime).
            // Waktu tambahan/buffer di luar end_time dihitung 0 jam unpaid.
            $totalMinutes = max(0, $clockInTime->diffInMinutes($shiftEndDateTime));
            $totalHours = round($totalMinutes / 60, 2);

            if ($setting && $setting->max_regular_hours !== null && (float) $setting->max_regular_hours <= 0) {
                $regularHours = $totalHours;
                $overtimeHours = 0.0;
            } else {
                $maxRegularHours = ($setting && $setting->max_regular_hours !== null)
                    ? (float) $setting->max_regular_hours
                    : 8.0;

                $regularHours = min($totalHours, $maxRegularHours);
                $overtimeHours = max(0.0, round($totalHours - $maxRegularHours, 2));
            }

            $oldValues = [
                'clock_out_time' => $assignment->clock_out_time?->toDateTimeString(),
                'status' => $assignment->status,
                'is_auto_clockout' => (bool) $assignment->is_auto_clockout,
                'regular_hours' => (float) $assignment->regular_hours,
                'overtime_hours' => (float) $assignment->overtime_hours,
            ];

            $newValues = [
                'clock_out_time' => $shiftEndDateTime->toDateTimeString(),
                'status' => 'completed',
                'is_auto_clockout' => true,
                'regular_hours' => $regularHours,
                'overtime_hours' => $overtimeHours,
            ];

            $assignment->update([
                'clock_out_time' => $shiftEndDateTime,
                'is_auto_clockout' => true,
                'status' => 'completed',
                'regular_hours' => $regularHours,
                'overtime_hours' => $overtimeHours,
            ]);

            AuditLog::create([
                'company_id' => $assignment->company_id,
                'user_id' => $assignment->user_id,
                'action' => 'auto_clockout',
                'model_type' => ShiftAssignment::class,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => '127.0.0.1',
            ]);
        });
    }

    /**
     * Hitung shift_end_datetime dari shift->start_time dan shift->end_time,
     * termasuk menangani shift lintas tengah malam (overnight shift).
     */
    public static function resolveShiftEndDateTime(Shift $shift, ?string $timezone = null): Carbon
    {
        $tz = $timezone ?? 'Asia/Jakarta';
        $start = Carbon::parse($shift->start_time, $tz);
        $end = Carbon::parse($shift->end_time, $tz);

        // Kasus 1: end_time lebih kecil dari start_time
        if ($end->lessThan($start)) {
            $end = $end->copy()->setDate($start->year, $start->month, $start->day)->addDay();
        }
        // Kasus 2: tanggal sama, tetapi jam end_time lebih awal dari jam start_time (shift malam)
        elseif ($end->format('Y-m-d') === $start->format('Y-m-d') && $end->format('H:i:s') < $start->format('H:i:s')) {
            $end = $end->copy()->addDay();
        }

        return $end;
    }
}
