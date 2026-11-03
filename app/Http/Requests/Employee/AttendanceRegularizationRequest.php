<?php

namespace App\Http\Requests\Employee;

use App\Console\Commands\AutoClockOutCommand;
use App\Models\AttendanceRegularization;
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

            // 2. Hanya shift dengan is_auto_clockout = true yang bisa diajukan
            if (! $assignment->is_auto_clockout) {
                $validator->errors()->add('shift_assignment_id', 'Hanya presensi yang terkena auto clock-out yang dapat diajukan koreksi.');

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

            $timezone = $user->company?->timezone ?? 'Asia/Jakarta';
            $shift = $assignment->shift;

            if ($shift) {
                $shiftEnd = AutoClockOutCommand::resolveShiftEndDateTime($shift, $timezone);
                $requestedOut = Carbon::parse($rawRequestedTime, $timezone);

                // Jam pulang yang diminta harus SETELAH end_time shift
                if ($requestedOut->lessThanOrEqualTo($shiftEnd)) {
                    $validator->errors()->add(
                        'requested_clock_out_time',
                        'Jam pulang riil harus setelah jam selesai shift resmi ('.$shiftEnd->format('H:i').' WIB).'
                    );
                }

                // Jam pulang tidak boleh lebih dari 24 jam setelah clock_in_time
                if ($assignment->clock_in_time) {
                    $clockIn = Carbon::parse($assignment->clock_in_time, $timezone);
                    if ($requestedOut->diffInHours($clockIn, false) < -24 || $requestedOut->lessThan($clockIn)) {
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
