<?php

namespace Tests\Feature\Security;

use App\Models\Company;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected User $userA;

    protected User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();

        $this->userA = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'employee',
        ]);

        $this->userB = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'employee',
        ]);
    }

    /**
     * TC-NOTIF-01: User A dilarang menandai sudah dibaca notifikasi milik User B (Anti-IDOR).
     */
    public function test_user_cannot_mark_as_read_another_users_notification(): void
    {
        $notifB = Notification::create([
            'company_id' => $this->company->id,
            'user_id' => $this->userB->id,
            'type' => 'shift_assigned',
            'title' => 'Shift Ditugaskan',
            'message' => 'Anda ditugaskan pada shift baru.',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->userA)
            ->patch(route('notifications.read', $notifB));

        // Wajib ditolak dengan 403 Forbidden
        $response->assertForbidden();

        $notifB->refresh();
        $this->assertFalse((bool) $notifB->is_read);
    }

    /**
     * TC-NOTIF-02: Aksi markAllAsRead hanya mengubah status notifikasi user yang login.
     */
    public function test_mark_all_as_read_only_affects_authenticated_users_notifications(): void
    {
        $notifA = Notification::create([
            'company_id' => $this->company->id,
            'user_id' => $this->userA->id,
            'type' => 'shift_assigned',
            'title' => 'Notif A',
            'message' => 'Pesan A',
            'is_read' => false,
        ]);

        $notifB = Notification::create([
            'company_id' => $this->company->id,
            'user_id' => $this->userB->id,
            'type' => 'shift_assigned',
            'title' => 'Notif B',
            'message' => 'Pesan B',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->userA)
            ->patch(route('notifications.read-all'));

        $response->assertRedirect();

        $notifA->refresh();
        $notifB->refresh();

        $this->assertTrue((bool) $notifA->is_read, 'Notifikasi User A harus berubah menjadi terbaca.');
        $this->assertFalse((bool) $notifB->is_read, 'Notifikasi User B tidak boleh ikut berubah.');
    }
}
