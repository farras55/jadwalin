<?php

namespace Tests\Unit\Services;

use App\Console\Commands\AutoClockOutCommand;
use App\Models\Shift;
use Tests\TestCase;

class OvernightShiftResolutionTest extends TestCase
{
    public function test_resolves_same_day_shift_end_datetime(): void
    {
        $shift = new Shift([
            'start_time' => '2026-11-04 08:00:00',
            'end_time' => '2026-11-04 17:00:00',
        ]);

        $resolved = AutoClockOutCommand::resolveShiftEndDateTime($shift, 'Asia/Jakarta');

        $this->assertEquals('2026-11-04 17:00:00', $resolved->format('Y-m-d H:i:s'));
    }

    public function test_resolves_overnight_shift_where_end_time_is_next_day(): void
    {
        $shift = new Shift([
            'start_time' => '2026-11-04 22:00:00',
            'end_time' => '2026-11-04 06:00:00',
        ]);

        $resolved = AutoClockOutCommand::resolveShiftEndDateTime($shift, 'Asia/Jakarta');

        // End time should be advanced to next day: 2026-11-05 06:00:00
        $this->assertEquals('2026-11-05 06:00:00', $resolved->format('Y-m-d H:i:s'));
    }

    public function test_resolves_overnight_shift_across_month_boundary(): void
    {
        $shift = new Shift([
            'start_time' => '2026-10-31 23:00:00',
            'end_time' => '2026-10-31 07:00:00',
        ]);

        $resolved = AutoClockOutCommand::resolveShiftEndDateTime($shift, 'Asia/Jakarta');

        $this->assertEquals('2026-11-01 07:00:00', $resolved->format('Y-m-d H:i:s'));
    }
}
