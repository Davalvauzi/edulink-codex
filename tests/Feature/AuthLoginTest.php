<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_is_redirected_to_admin_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_guru_cannot_open_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'guru',
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertRedirect(route('guru.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_guest_is_redirected_to_login_page_when_opening_siswa_dashboard(): void
    {
        $response = $this->get('/siswa/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_guest_can_register_as_siswa_with_kelas(): void
    {
        $response = $this->post('/register', [
            'name' => 'Siswa Baru',
            'email' => 'siswa-baru@example.com',
            'kelas' => '11',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'siswa-baru@example.com',
            'role' => 'siswa',
            'kelas' => '11',
        ]);
    }

    public function test_siswa_can_update_profile_from_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'siswa',
            'kelas' => '10',
            'email' => 'siswa-lama@example.com',
        ]);

        $response = $this->actingAs($user)->put('/siswa/profile', [
            'name' => 'Siswa Update',
            'email' => 'siswa-update@example.com',
            'kelas' => '12',
            'password' => 'password999',
            'password_confirmation' => 'password999',
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Siswa Update',
            'email' => 'siswa-update@example.com',
            'kelas' => '12',
        ]);
    }
}
