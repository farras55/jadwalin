<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Employee\AttendanceRegularizationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AttendanceRegularizationRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_rules_contains_required_fields(): void
    {
        $request = new AttendanceRegularizationRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('shift_assignment_id', $rules);
        $this->assertArrayHasKey('requested_clock_out_time', $rules);
        $this->assertArrayHasKey('reason', $rules);
    }

    public function test_validation_fails_when_reason_is_too_short(): void
    {
        $request = new AttendanceRegularizationRequest;

        $validator = Validator::make([
            'shift_assignment_id' => 1,
            'requested_clock_out_time' => '2026-11-04 18:00:00',
            'reason' => 'Pendek', // < 10 chars
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('reason', $validator->errors()->toArray());
    }

    public function test_authorize_allows_only_employee_role(): void
    {
        $employee = new User(['role' => 'employee']);
        $manager = new User(['role' => 'manager']);

        $request = new AttendanceRegularizationRequest;

        $request->setUserResolver(fn () => $employee);
        $this->assertTrue($request->authorize());

        $request->setUserResolver(fn () => $manager);
        $this->assertFalse($request->authorize());
    }
}
