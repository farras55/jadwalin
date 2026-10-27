<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ShiftAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display the employee's schedule calendar for a given week.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Determine the week offset (0 = current week, negative = past, positive = future)
        $weekOffset = (int) $request->get('week_offset', 0);

        // Calculate the start and end of the target week (Mon–Sun)
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->addWeeks($weekOffset)->startOfDay();
        $weekEnd   = $weekStart->copy()->addDays(6)->endOfDay();

        // Fetch only the authenticated employee's assignments for published shifts in the target week
        $assignments = ShiftAssignment::with([
                'shift',
                'shift.template',
                'shift.company',
                // Load other assignments on same shifts for coworker display (no N+1)
                'shift.shiftAssignments.user',
            ])
            ->where('user_id', $user->id)
            ->whereHas('shift', function ($query) use ($weekStart, $weekEnd) {
                $query->where('status', 'published')
                      ->whereBetween('start_time', [$weekStart, $weekEnd]);
            })
            ->get()
            ->sortBy(fn ($a) => optional($a->shift)->start_time);

        // Build a day-indexed map for the 7-day grid (Mon=0 … Sun=6)
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[$i] = [
                'date'        => $weekStart->copy()->addDays($i),
                'assignments' => collect(),
            ];
        }

        $now = Carbon::now();

        foreach ($assignments as $assignment) {
            $shiftStart = optional($assignment->shift)->start_time;
            if (!$shiftStart) {
                continue;
            }

            // Determine which day slot (0–6)
            $dayIndex = $shiftStart->copy()->startOfDay()->diffInDays($weekStart->copy()->startOfDay());
            if ($dayIndex < 0 || $dayIndex > 6) {
                continue;
            }

            // Determine schedule status
            $shiftEnd = optional($assignment->shift)->end_time;
            if ($now->lt($shiftStart)) {
                $scheduleStatus = 'upcoming';
            } elseif ($shiftEnd && $now->gt($shiftEnd)) {
                $scheduleStatus = 'completed';
            } else {
                $scheduleStatus = 'ongoing';
            }

            // Build coworkers list (exclude current user)
            $coworkers = optional($assignment->shift)->shiftAssignments
                ->where('user_id', '!=', $user->id)
                ->map(fn ($a) => optional($a->user)->name)
                ->filter()
                ->values();

            // Attach computed data as transient properties
            $assignment->schedule_status   = $scheduleStatus;
            $assignment->coworkers_list    = $coworkers;
            $assignment->location          = optional(optional($assignment->shift)->company)->address ?? '-';
            $assignment->template_color    = optional(optional($assignment->shift)->template)->color_code ?? '#6C5CE7';

            $days[$dayIndex]['assignments']->push($assignment);
        }

        // Week label: "27 Okt - 02 Nov 2026"
        $weekLabel = $weekStart->translatedFormat('d M') . ' - ' . $weekEnd->translatedFormat('d M Y');

        return view('employee.schedule.index', compact(
            'days',
            'weekStart',
            'weekEnd',
            'weekLabel',
            'weekOffset',
            'now',
        ));
    }

    /**
     * Display the detail of a single shift assignment.
     */
    public function show(ShiftAssignment $assignment)
    {
        $user = auth()->user();

        // Only the owner can view
        if ($assignment->user_id !== $user->id) {
            abort(403);
        }

        // Do not expose draft shifts
        if (optional($assignment->shift)->status === 'draft') {
            abort(404);
        }

        $assignment->load([
            'shift',
            'shift.template',
            'shift.company',
            'shift.shiftAssignments.user',
        ]);

        $now = Carbon::now();
        $shiftStart = optional($assignment->shift)->start_time;
        $shiftEnd   = optional($assignment->shift)->end_time;

        if ($now->lt($shiftStart)) {
            $scheduleStatus = 'upcoming';
        } elseif ($shiftEnd && $now->gt($shiftEnd)) {
            $scheduleStatus = 'completed';
        } else {
            $scheduleStatus = 'ongoing';
        }

        $coworkers = optional($assignment->shift)->shiftAssignments
            ->where('user_id', '!=', $user->id)
            ->map(fn ($a) => optional($a->user)->name)
            ->filter()
            ->values();

        $location       = optional(optional($assignment->shift)->company)->address ?? '-';
        $templateColor  = optional(optional($assignment->shift)->template)->color_code ?? '#6C5CE7';

        return view('employee.schedule.show', compact(
            'assignment',
            'scheduleStatus',
            'coworkers',
            'location',
            'templateColor',
            'now',
        ));
    }
}
