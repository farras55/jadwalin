<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function createCompany(): Company
    {
        return Company::create([
            'name' => 'Test Company',
            'email' => 'testcompany@example.com',
            'phone_number' => '081234567890',
            'address' => 'Jl. Test No. 1, Malang',
        ]);
    }

    private function createUser(Company $company, string $role = 'employee'): User
    {
        return User::factory()->create([
            'company_id' => $company->id,
            'role' => $role,
            'status' => 'active',
        ]);
    }

    public function test_user_can_view_notifications_list_and_unread_count(): void
    {
        $company = $this->createCompany();
        $user = $this->createUser($company);

        Notification::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'type' => 'shift',
            'title' => 'Shift Baru',
            'message' => 'Anda mendapatkan shift baru.',
            'is_read' => false,
        ]);

        Notification::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'type' => 'info',
            'title' => 'Informasi',
            'message' => 'Informasi untuk Anda.',
            'is_read' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewIs('notifications.index');
        $response->assertViewHas('unreadNotifCount', 1);
        $response->assertSee('Shift Baru');
        $response->assertSee('Anda mendapatkan shift baru.');
    }

    public function test_user_can_mark_single_notification_as_read(): void
    {
        $company = $this->createCompany();
        $user = $this->createUser($company);

        $notification = Notification::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'type' => 'shift',
            'title' => 'Shift Baru',
            'message' => 'Anda mendapatkan shift baru.',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)
            ->patch(route('notifications.read', $notification));

        $response->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'user_id' => $user->id,
            'is_read' => true,
        ]);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $company = $this->createCompany();
        $user = $this->createUser($company);

        Notification::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'type' => 'shift',
            'title' => 'Shift 1',
            'message' => 'Notifikasi shift pertama.',
            'is_read' => false,
        ]);

        Notification::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'type' => 'swap',
            'title' => 'Swap Shift',
            'message' => 'Permintaan swap shift.',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)
            ->patch(route('notifications.read-all'));

        $response->assertRedirect();

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $user->id,
            'is_read' => false,
        ]);
    }

    public function test_notification_service_creates_records_correctly(): void
    {
        $company = $this->createCompany();
        $user = $this->createUser($company);

        $notification = NotificationService::send(
            $user->id,
            'shift',
            'Shift Dipublikasikan',
            'Shift baru telah dipublikasikan.'
        );

        $this->assertInstanceOf(Notification::class, $notification);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'company_id' => $company->id,
            'user_id' => $user->id,
            'type' => 'shift',
            'title' => 'Shift Dipublikasikan',
            'message' => 'Shift baru telah dipublikasikan.',
            'is_read' => false,
        ]);
    }
}