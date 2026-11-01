<?php

namespace Tests\Feature\Performance;

use App\Models\Availability;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\Position;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\ShiftTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NPlusOneQueryTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Department $departmentA;

    protected Department $departmentB;

    protected Position $position1;

    protected Position $position2;

    protected User $superadmin;

    protected User $manager;

    protected array $employees = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Enforce strict prevention of lazy loading
        Model::preventLazyLoading(true);

        $this->company = Company::forceCreate([
            'name' => 'PT Test Performansi',
            'email' => 'admin@performansi.com',
            'phone_number' => '081299998888',
            'address' => 'Gedung Cyber 2 Lt 10',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);

        CompanySetting::forceCreate([
            'company_id' => $this->company->id,
            'max_regular_hours' => 40,
            'max_overtime_hours' => 18,
            'grace_period_minutes' => 15,
            'auto_buffer_minutes' => 30,
        ]);

        $this->departmentA = Department::forceCreate([
            'company_id' => $this->company->id,
            'name' => 'Operasional Bar',
        ]);

        $this->departmentB = Department::forceCreate([
            'company_id' => $this->company->id,
            'name' => 'Kitchen & Bakery',
        ]);

        $this->position1 = Position::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
            'name' => 'Head Barista',
        ]);

        $this->position2 = Position::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->departmentB->id,
            'name' => 'Pastry Chef',
        ]);

        $this->superadmin = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->manager = User::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
            'role' => 'manager',
            'status' => 'active',
        ]);

        // Buat 10 karyawan aktif
        for ($i = 1; $i <= 10; $i++) {
            $dept = ($i % 2 === 0) ? $this->departmentA : $this->departmentB;
            $pos = ($i % 2 === 0) ? $this->position1 : $this->position2;

            $this->employees[] = User::factory()->create([
                'company_id' => $this->company->id,
                'department_id' => $dept->id,
                'position_id' => $pos->id,
                'role' => 'employee',
                'status' => 'active',
            ]);
        }

        // Buat Template Shift
        $template = ShiftTemplate::forceCreate([
            'company_id' => $this->company->id,
            'department_id' => $this->departmentA->id,
            'title' => 'Shift Pagi Reguler',
            'start_time' => '08:00',
            'end_time' => '16:00',
            'default_quota' => 2,
            'color_code' => '#6C5CE7',
        ]);

        // Buat 10 Shift beserta Assignments
        for ($s = 0; $s < 10; $s++) {
            $shift = Shift::forceCreate([
                'company_id' => $this->company->id,
                'department_id' => $this->departmentA->id,
                'template_id' => $template->id,
                'title' => "Shift {$s}",
                'start_time' => Carbon::today()->addDays($s)->setTime(8, 0),
                'end_time' => Carbon::today()->addDays($s)->setTime(16, 0),
                'quota' => 2,
                'status' => 'published',
                'is_open_shift' => ($s === 0),
            ]);

            // Assign 2 karyawan per shift
            ShiftAssignment::forceCreate([
                'company_id' => $this->company->id,
                'user_id' => $this->employees[0]->id,
                'shift_id' => $shift->id,
                'clock_in_time' => Carbon::today()->setTime(8, 0),
                'clock_out_time' => Carbon::today()->setTime(16, 0),
                'regular_hours' => 8,
                'overtime_hours' => 0,
                'status' => 'present',
            ]);

            ShiftAssignment::forceCreate([
                'company_id' => $this->company->id,
                'user_id' => $this->employees[1]->id,
                'shift_id' => $shift->id,
                'clock_in_time' => null,
                'clock_out_time' => null,
                'status' => 'scheduled',
            ]);
        }

        // Buat data Availabilities
        foreach (array_slice($this->employees, 0, 5) as $emp) {
            Availability::forceCreate([
                'company_id' => $this->company->id,
                'user_id' => $emp->id,
                'start_time' => Carbon::today()->setTime(9, 0),
                'end_time' => Carbon::today()->setTime(12, 0),
                'reason' => 'Kuliah pagi',
                'status' => 'pending_manager',
            ]);
        }
    }

    public function test_superadmin_views_have_no_n_plus_one_violations(): void
    {
        $endpoints = [
            'admin.dashboard' => route('admin.dashboard'),
            'admin.departments.index' => route('admin.departments.index'),
            'admin.positions.index' => route('admin.positions.index'),
            'admin.shifts.index' => route('admin.shifts.index'),
            'admin.reports.timesheet' => route('admin.reports.timesheet'),
        ];

        foreach ($endpoints as $name => $url) {
            DB::flushQueryLog();
            DB::enableQueryLog();

            $response = $this->actingAs($this->superadmin)->get($url);
            $response->assertOk();

            $queries = DB::getQueryLog();
            DB::disableQueryLog();

            // Memastikan jumlah query tetap konstan dan wajar (< 25 query)
            $this->assertLessThan(
                25,
                count($queries),
                "Terlalu banyak query pada endpoint Superadmin [{$url}]: ".count($queries)
            );
        }
    }

    public function test_manager_views_have_no_n_plus_one_violations(): void
    {
        $endpoints = [
            'manager.dashboard' => route('manager.dashboard'),
            'manager.shifts.index' => route('manager.shifts.index'),
            'manager.employees.index' => route('manager.employees.index'),
            'manager.attendance.index' => route('manager.attendance.index'),
            'manager.availability.index' => route('manager.availability.index'),
            'manager.swaps.index' => route('manager.swaps.index'),
        ];

        foreach ($endpoints as $name => $url) {
            DB::flushQueryLog();
            DB::enableQueryLog();

            $response = $this->actingAs($this->manager)->get($url);
            $response->assertOk();

            $queries = DB::getQueryLog();
            DB::disableQueryLog();

            $this->assertLessThan(
                30,
                count($queries),
                "Terlalu banyak query pada endpoint Manager [{$url}]: ".count($queries)
            );
        }
    }

    public function test_employee_views_have_no_n_plus_one_violations(): void
    {
        $employee = $this->employees[0];

        $endpoints = [
            'employee.dashboard' => route('employee.dashboard'),
            'employee.schedule' => route('employee.schedule'),
            'employee.attendance' => route('employee.attendance'),
            'employee.availability' => route('employee.availability'),
            'employee.swaps' => route('employee.swaps'),
            'employee.open-shifts' => route('employee.open-shifts'),
        ];

        foreach ($endpoints as $name => $url) {
            DB::flushQueryLog();
            DB::enableQueryLog();

            $response = $this->actingAs($employee)->get($url);
            $response->assertOk();

            $queries = DB::getQueryLog();
            DB::disableQueryLog();

            $this->assertLessThan(
                25,
                count($queries),
                "Terlalu banyak query pada endpoint Employee [{$url}]: ".count($queries)
            );
        }
    }
}
