<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountStatusAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-AUTH-01: Pengguna berstatus aktif dapat login secara normal.
     */
    public function test_active_user_can_authenticate_successfully(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'status' => 'active',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    /**
     * TC-AUTH-02 (Celah Akun Nonaktif): Pengguna yang dinonaktifkan (status = 'resigned')
     * DILARANG login ke dalam sistem, meskipun kredensial email & password valid.
     */
    public function test_resigned_user_cannot_authenticate(): void
    {
        $company = Company::factory()->create();
        $resignedUser = User::factory()->create([
            'company_id' => $company->id,
            'status' => 'resigned',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $resignedUser->email,
            'password' => 'password123',
        ]);

        // Ekspektasi: Sistem yang aman menolak otentikasi akun nonaktif/resigned
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
