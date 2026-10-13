<?php

namespace Tests\Feature\Employee;

use App\Models\Availability;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::forceCreate([
            'name' => 'Test Company',
            'email' => 'test@company.com',
            'phone_number' => '081234567890',
            'address' => 'Jl. Test No. 1',
        ]);

        CompanySetting::forceCreate([
            'company_id' => $this->company->id,
            'auto_buffer_minutes' => 30,
        ]);

        $this->user = User::factory()->create([
            'role' => 'employee',
            'company_id' => $this->company->id,
        ]);

        // Selasa, 13 Oktober 2026 pukul 09:00
        Carbon::setTestNow(Carbon::create(2026, 10, 13, 9, 0, 0));
    }

    private function makeAvailability(User $owner, string $status = 'pending_manager', string $reason = 'Kuliah Praktikum'): Availability
    {
        return Availability::forceCreate([
            'company_id' => $owner->company_id,
            'user_id' => $owner->id,
            'is_recurring' => false,
            'day_of_week' => null,
            'start_time' => Carbon::create(2026, 10, 14, 12, 30),
            'end_time' => Carbon::create(2026, 10, 14, 15, 30),
            'reason' => $reason,
            'status' => $status,
        ]);
    }

    public function test_employee_can_view_own_availability_list()
    {
        $otherUser = User::factory()->create([
            'role' => 'employee',
            'company_id' => $this->company->id,
        ]);

        $this->makeAvailability($this->user, 'pending_manager', 'Kuliah Praktikum Saya');
        $this->makeAvailability($otherUser, 'pending_manager', 'Urusan Orang Lain');

        $response = $this->actingAs($this->user)->get(route('employee.availability'));

        $response->assertStatus(200);
        $response->assertViewIs('employee.availability.index');
        $response->assertSee('Kuliah Praktikum Saya');
        $response->assertDontSee('Urusan Orang Lain');
    }

    public function test_employee_can_submit_unavailability_with_auto_buffer_applied()
    {
        $response = $this->actingAs($this->user)->post(route('employee.availability.store'), [
            'date' => '2026-10-14',
            'start_time' => '13:00',
            'end_time' => '15:00',
            'reason' => 'Kuliah Praktikum',
            'is_recurring' => '0',
        ]);

        $response->assertRedirect(route('employee.availability'));
        $response->assertSessionHas('success');

        $availability = Availability::first();

        $this->assertNotNull($availability);
        // 13:00 - 15:00 + buffer 30 menit => 12:30 - 15:30 (SDD TC-03)
        $this->assertEquals('2026-10-14 12:30', $availability->start_time->format('Y-m-d H:i'));
        $this->assertEquals('2026-10-14 15:30', $availability->end_time->format('Y-m-d H:i'));
        $this->assertEquals('pending_manager', $availability->status);
        $this->assertEquals($this->company->id, $availability->company_id);
        $this->assertEquals($this->user->id, $availability->user_id);
        $this->assertFalse($availability->is_recurring);
        $this->assertNull($availability->day_of_week);
    }

    public function test_recurring_submission_stores_day_of_week()
    {
        $this->actingAs($this->user)->post(route('employee.availability.store'), [
            'date' => '2026-10-14', // Rabu
            'start_time' => '08:00',
            'end_time' => '12:00',
            'reason' => 'Kuliah Rutin',
            'is_recurring' => '1',
        ])->assertRedirect(route('employee.availability'));

        $availability = Availability::first();

        $this->assertTrue($availability->is_recurring);
        $this->assertEquals(3, $availability->day_of_week);
        $this->assertEquals('07:30', $availability->start_time->format('H:i'));
        $this->assertEquals('12:30', $availability->end_time->format('H:i'));
    }

    public function test_submission_with_past_date_or_inverted_time_fails()
    {
        // Tanggal sudah lewat
        $past = $this->actingAs($this->user)->post(route('employee.availability.store'), [
            'date' => '2026-10-12',
            'start_time' => '13:00',
            'end_time' => '15:00',
            'reason' => 'Kuliah Praktikum',
        ]);
        $past->assertSessionHasErrors('date');

        // Jam selesai lebih awal dari jam mulai
        $inverted = $this->actingAs($this->user)->post(route('employee.availability.store'), [
            'date' => '2026-10-14',
            'start_time' => '15:00',
            'end_time' => '13:00',
            'reason' => 'Kuliah Praktikum',
        ]);
        $inverted->assertSessionHasErrors('end_time');

        // Alasan wajib diisi
        $noReason = $this->actingAs($this->user)->post(route('employee.availability.store'), [
            'date' => '2026-10-14',
            'start_time' => '13:00',
            'end_time' => '15:00',
            'reason' => '',
        ]);
        $noReason->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('availabilities', 0);
    }

    public function test_employee_can_delete_pending_availability_only()
    {
        $availability = $this->makeAvailability($this->user, 'pending_manager');

        $response = $this->actingAs($this->user)
            ->delete(route('employee.availability.destroy', $availability));

        $response->assertRedirect(route('employee.availability'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('availabilities', ['id' => $availability->id]);
    }

    public function test_employee_cannot_delete_already_approved_availability()
    {
        $availability = $this->makeAvailability($this->user, 'approved');

        $response = $this->actingAs($this->user)
            ->delete(route('employee.availability.destroy', $availability));

        $response->assertRedirect(route('employee.availability'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('availabilities', [
            'id' => $availability->id,
            'status' => 'approved',
        ]);
    }

    public function test_employee_cannot_delete_another_users_availability()
    {
        $otherUser = User::factory()->create([
            'role' => 'employee',
            'company_id' => $this->company->id,
        ]);
        $availability = $this->makeAvailability($otherUser, 'pending_manager');

        $response = $this->actingAs($this->user)
            ->delete(route('employee.availability.destroy', $availability));

        $response->assertForbidden();
        $this->assertDatabaseHas('availabilities', ['id' => $availability->id]);
    }
}