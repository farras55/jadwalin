<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $company = Company::create([
            'name' => 'PT Jadwalin Digital',
            'email' => 'info@jadwalin.com',
            'phone_number' => '081234567890',
            'address' => 'Jl. Soekarno Hatta No. 9 Malang',
            'timezone' => 'Asia/Jakarta',
            'status' => 'active',
        ]);

        $dept = Department::create([
            'company_id' => $company->id,
            'name' => 'Engineering',
        ]);

        $pos = Position::create([
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'name' => 'Backend Developer',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'department_id' => $dept->id,
            'position_id' => $pos->id,
            'name' => 'Farras Alwi',
            'phone_number' => '081234567890',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee('Farras Alwi');
        $response->assertSee('PT Jadwalin Digital');
        $response->assertSee('Engineering');
        $response->assertSee('Backend Developer');
    }

    public function test_user_can_update_profile_information_and_phone(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'phone_number' => '08111111111',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'phone_number' => '081298765432',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertSame('081298765432', $user->phone_number);
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_can_upload_valid_profile_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $avatar = UploadedFile::fake()->image('avatar.jpg', 400, 400)->size(500);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => '081234567890',
                'profile_photo' => $avatar,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertNotNull($user->profile_photo);
        Storage::disk('public')->assertExists($user->profile_photo);

        // Upload a second avatar and ensure old one is deleted
        $oldPhotoPath = $user->profile_photo;
        $newAvatar = UploadedFile::fake()->image('new_avatar.png', 400, 400)->size(600);

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => '081234567890',
                'profile_photo' => $newAvatar,
            ]);

        $user->refresh();
        Storage::disk('public')->assertMissing($oldPhotoPath);
        Storage::disk('public')->assertExists($user->profile_photo);
    }

    public function test_invalid_avatar_format_or_oversize_is_rejected(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        // 1. Oversize image (> 2048 KB)
        $oversizeFile = UploadedFile::fake()->image('large.jpg')->size(3000);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo' => $oversizeFile,
            ]);

        $response->assertSessionHasErrors(['profile_photo']);

        // 2. Non-image file (e.g. PDF)
        $pdfFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response2 = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo' => $pdfFile,
            ]);

        $response2->assertSessionHasErrors(['profile_photo']);
    }

    public function test_invalid_phone_number_format_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => 'invalid-phone-123',
            ]);

        $response->assertSessionHasErrors(['phone_number']);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertTrue($user->fresh()->trashed());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}