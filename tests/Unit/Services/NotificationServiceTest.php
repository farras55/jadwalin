<?php

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_creates_notification_using_user_instance(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        $notification = NotificationService::send(
            $user,
            'shift',
            'Penugasan Shift Baru',
            'Anda dijadwalkan shift pagi besok.'
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($user->id, $notification->user_id);
        $this->assertEquals($company->id, $notification->company_id);
        $this->assertEquals('shift', $notification->type);
        $this->assertEquals('Penugasan Shift Baru', $notification->title);
        $this->assertFalse($notification->is_read);
    }

    public function test_send_creates_notification_using_user_id_and_explicit_company_id(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        $notification = NotificationService::send(
            $user->id,
            'swap',
            'Permintaan Tukar Shift',
            'Rekan kerja mengajukan tukar shift.',
            $company->id
        );

        $this->assertEquals($user->id, $notification->user_id);
        $this->assertEquals($company->id, $notification->company_id);
    }
}
