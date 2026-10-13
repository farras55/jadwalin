<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\CompanySetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AvailabilityController extends Controller
{

    private const DEFAULT_BUFFER_MINUTES = 30;

    public function index(): View
    {
        $user = auth()->user();

        $availabilities = Availability::where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->orderByDesc('start_time')
            ->get();

        return view('employee.availability.index', [
            'availabilities' => $availabilities,
            'bufferMinutes' => $this->bufferMinutes($user),
        ]);
    }

    public function create(): View
    {
        return view('employee.availability.create', [
            'bufferMinutes' => $this->bufferMinutes(auth()->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:255'],
            'is_recurring' => ['nullable', 'boolean'],
        ], [
            'date.required' => 'Tanggal wajib diisi.',
            'date.date_format' => 'Format tanggal tidak valid.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'start_time.date_format' => 'Format jam mulai tidak valid.',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'end_time.date_format' => 'Format jam selesai tidak valid.',
            'reason.required' => 'Alasan wajib diisi.',
            'reason.max' => 'Alasan maksimal 255 karakter.',
        ]);

        $start = Carbon::createFromFormat('Y-m-d H:i', $validated['date'] . ' ' . $validated['start_time']);
        $end = Carbon::createFromFormat('Y-m-d H:i', $validated['date'] . ' ' . $validated['end_time']);

        if ($start->lessThanOrEqualTo(now())) {
            throw ValidationException::withMessages([
                'date' => 'Waktu mulai halangan harus di masa depan.',
            ]);
        }

        if ($end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages([
                'end_time' => 'Jam selesai harus setelah jam mulai.',
            ]);
        }

        $buffer = $this->bufferMinutes($user);
        $isRecurring = $request->boolean('is_recurring');

        Availability::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'is_recurring' => $isRecurring,
            // 1 (Senin) s.d. 7 (Minggu), dihitung dari tanggal yang dipilih.
            'day_of_week' => $isRecurring ? $start->dayOfWeekIso : null,
            // Auto-buffer: waktu yang tersimpan sudah termasuk buffer.
            'start_time' => $start->copy()->subMinutes($buffer),
            'end_time' => $end->copy()->addMinutes($buffer),
            'reason' => $validated['reason'],
            'status' => 'pending_manager',
        ]);

        return redirect()
            ->route('employee.availability')
            ->with('success', 'Halangan berhasil diajukan dan menunggu persetujuan manajer.');
    }

    public function destroy(Availability $availability): RedirectResponse
    {
        $user = auth()->user();

        abort_unless(
            $availability->user_id === $user->id && $availability->company_id === $user->company_id,
            403
        );

        if ($availability->status !== 'pending_manager') {
            return redirect()
                ->route('employee.availability')
                ->with('error', 'Hanya pengajuan yang masih menunggu manajer yang dapat dibatalkan.');
        }

        $availability->delete();

        return redirect()
            ->route('employee.availability')
            ->with('success', 'Pengajuan halangan berhasil dibatalkan.');
    }

    private function bufferMinutes(User $user): int
    {
        $setting = CompanySetting::where('company_id', $user->company_id)->first();

        return (int) ($setting->auto_buffer_minutes ?? self::DEFAULT_BUFFER_MINUTES);
    }
}