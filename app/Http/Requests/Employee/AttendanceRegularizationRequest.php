<?php

namespace App\Http\Requests\Employee;

use App\Console\Commands\AutoClockOutCommand;
use App\Models\AttendanceRegularization;
use App\Models\AuditLog;
use App\Models\ShiftAssignment;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttendanceRegularizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'employee';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shift_assignment_id' => ['required', 'integer', 'exists:shift_assignments,id'],
            'requested_clock_out_time' => ['required', 'date'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'shift_assignment_id.required' => 'Shift assignment wajib dipilih.',
            'shift_assignment_id.exists' => 'Data shift tidak valid.',
            'requested_clock_out_time.required' => 'Jam pulang riil wajib diisi.',
            'requested_clock_out_time.date' => 'Format jam pulang riil tidak valid.',
            'reason.required' => 'Alasan koreksi kehadiran wajib diisi.',
            'reason.min' => 'Alasan minimal 10 karakter.',
            'reason.max' => 'Alasan maksimal 1000 karakter.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();
            if (! $user) {
                return;
            }

            $assignmentId = $this->input('shift_assignment_id');
            $assignment = ShiftAssignment::with('shift')->find($assignmentId);

            if (! $assignment) {
                return;
            }

            // 1. Karyawan hanya bisa mengajukan untuk shift miliknya sendiri
            if ($assignment->user_id !== $user->id) {
                $validator->errors()->add('shift_assignment_id', 'Anda tidak memiliki akses untuk mengajukan koreksi pada shift ini.');

                return;
            }

            // 2. Hanya shift dengan catatan clock in yang bisa diajukan koreksi
            if (! $assignment->clock_in_time) {
                $validator->errors()->add('shift_assignment_id', 'Hanya presensi yang memiliki catatan kehadiran yang dapat diajukan koreksi.');

                return;
            }

            $timezone = $user->company?->timezone ?? 'Asia/Jakarta';
            $shift = $assignment->shift;
            $shiftEnd = $shift ? AutoClockOutCommand::resolveShiftEndDateTime($shift, $timezone) : null;
            $isLateClockOut = $assignment->clock_out_time && $shiftEnd && Carbon::parse($assignment->clock_out_time, $timezone)->greaterThan($shiftEnd);

            if (! $assignment->is_auto_clockout && ! $isLateClockOut) {
                $validator->errors()->add('shift_assignment_id', 'Hanya presensi yang terkena auto clock-out atau memiliki kelebihan jam kerja yang dapat diajukan koreksi.');

                return;
            }

            // 3. Cegah pengajuan ganda saat masih ada pengajuan berstatus pending
            $hasPending = AttendanceRegularization::where('shift_assignment_id', $assignment->id)
                ->where('status', 'pending')
                ->exists();

            if ($hasPending) {
                $validator->errors()->add('shift_assignment_id', 'Pengajuan koreksi kehadiran untuk shift ini sudah ada dan sedang menunggu persetujuan manajer.');

                return;
            }

            // 4. Validasi requested_clock_out_time
            $rawRequestedTime = $this->input('requested_clock_out_time');
            if (! $rawRequestedTime) {
                return;
            }

            if ($shift) {
                $requestedOut = Carbon::parse($rawRequestedTime, $timezone);

                $tzAbbr = AuditLog::getTimezoneAbbreviation($timezone);

                // Jam pulang yang diminta harus SETELAH end_time shift resmi
                if ($shiftEnd && $requestedOut->lessThanOrEqualTo($shiftEnd)) {
                    $validator->errors()->add(
                        'requested_clock_out_time',
                        'Jam pulang riil harus setelah jam selesai shift resmi ('.$shiftEnd->format('H:i').' '.$tzAbbr.').'
                    );
                }

                // Jam pulang tidak boleh di masa depan
                if ($requestedOut->isFuture()) {
                    $validator->errors()->add(
                        'requested_clock_out_time',
                        'Jam pulang riil tidak boleh di masa depan.'
                    );
                }

                // Jam pulang tidak boleh sebelum clock_in_time atau melebihi 24 jam dari clock in
                if ($assignment->clock_in_time) {
                    $clockIn = Carbon::parse($assignment->clock_in_time, $timezone);
                    if ($requestedOut->lessThan($clockIn)) {
                        $validator->errors()->add(
                            'requested_clock_out_time',
                            'Jam pulang riil tidak boleh lebih awal dari jam clock-in ('.$clockIn->format('H:i').' '.$tzAbbr.').'
                        );
                    }

                    if ($requestedOut->diffInHours($clockIn, false) < -24) {
                        $validator->errors()->add(
                            'requested_clock_out_time',
                            'Jam pulang riil tidak masuk akal (melebihi 24 jam dari jam clock in).'
                        );
                    }
                }
            }
        });
    }
}
