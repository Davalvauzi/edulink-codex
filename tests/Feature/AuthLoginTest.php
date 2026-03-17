<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_siswa_can_update_profile_from_profile_page(): void
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

        $response->assertRedirect(route('siswa.profile'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Siswa Update',
            'email' => 'siswa-update@example.com',
            'kelas' => '12',
        ]);
    }

    public function test_siswa_dashboard_defaults_to_user_kelas_subjects(): void
    {
        $user = User::factory()->create([
            'role' => 'siswa',
            'kelas' => '11',
        ]);

        Subject::query()->create(['name' => 'Fisika Lanjut', 'kelas' => '11']);
        Subject::query()->create(['name' => 'Ekonomi Dasar', 'kelas' => '10']);

        $response = $this->actingAs($user)->get('/siswa/dashboard');

        $response->assertOk();
        $response->assertSee('Fisika Lanjut');
        $response->assertDontSee('Ekonomi Dasar');
        $response->assertSee('Menampilkan kelas 11');
    }

    public function test_siswa_can_filter_subjects_to_other_kelas(): void
    {
        $user = User::factory()->create([
            'role' => 'siswa',
            'kelas' => '11',
        ]);

        Subject::query()->create(['name' => 'Sosiologi Global', 'kelas' => '12']);
        Subject::query()->create(['name' => 'Sejarah Nusantara', 'kelas' => '11']);

        $response = $this->actingAs($user)->get('/siswa/dashboard?kelas=12');

        $response->assertOk();
        $response->assertSee('Sosiologi Global');
        $response->assertDontSee('Sejarah Nusantara');
        $response->assertSee('Menampilkan kelas 12');
    }

    public function test_guru_can_add_subject_for_specific_kelas(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $response = $this->actingAs($guru)->post(route('guru.subjects.store'), [
            'name' => 'Kimia',
            'kelas' => '10',
        ]);

        $subject = Subject::query()->where('name', 'Kimia')->firstOrFail();

        $response->assertRedirect(route('subjects.show', $subject));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('subjects', [
            'name' => 'Kimia',
            'kelas' => '10',
            'created_by' => $guru->id,
        ]);
    }

    public function test_guru_can_add_material_to_subject(): void
    {
        Storage::fake('public');

        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Matematika',
            'kelas' => '11',
            'created_by' => $guru->id,
        ]);

        $response = $this->actingAs($guru)->post(route('guru.subjects.materials.store', $subject), [
            'title' => 'Bab 1',
            'description' => 'Pengenalan aljabar',
            'file' => UploadedFile::fake()->create('bab-1.pdf', 100, 'application/pdf'),
        ]);

        $material = Material::query()->where('subject_id', $subject->id)->where('title', 'Bab 1')->firstOrFail();

        $response->assertRedirect(route('materials.show', [$subject, $material]));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('materials', [
            'subject_id' => $subject->id,
            'title' => 'Bab 1',
            'description' => 'Pengenalan aljabar',
            'created_by' => $guru->id,
        ]);
    }

    public function test_siswa_can_open_profile_page_from_dedicated_route(): void
    {
        $user = User::factory()->create([
            'role' => 'siswa',
            'kelas' => '10',
        ]);

        $response = $this->actingAs($user)->get(route('siswa.profile'));

        $response->assertOk();
        $response->assertSee('Profil Siswa');
        $response->assertSee('Perbarui Profil');
    }

    public function test_guru_can_open_dedicated_subject_create_page(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $response = $this->actingAs($guru)->get(route('guru.subjects.create'));

        $response->assertOk();
        $response->assertSee('Tambah Mata Pelajaran');
    }

    public function test_guru_can_open_dedicated_material_create_page(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Biologi',
            'kelas' => '10',
            'created_by' => $guru->id,
        ]);

        $response = $this->actingAs($guru)->get(route('guru.subjects.materials.create', $subject));

        $response->assertOk();
        $response->assertSee('Tambah Materi Baru');
        $response->assertSee('Biologi');
    }

    public function test_material_has_dedicated_detail_page(): void
    {
        $siswa = User::factory()->create([
            'role' => 'siswa',
            'kelas' => '11',
        ]);

        $subject = Subject::query()->create([
            'name' => 'Sejarah',
            'kelas' => '11',
        ]);

        $material = Material::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Bab 2',
            'description' => '<h2>Bab 2</h2><p><strong>Isi materi</strong></p>',
        ]);

        $response = $this->actingAs($siswa)->get(route('materials.show', [$subject, $material]));

        $response->assertOk();
        $response->assertSee('Bab 2');
        $response->assertSee('Isi materi', false);
    }

    public function test_guru_can_update_material(): void
    {
        Storage::fake('public');

        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Bahasa Indonesia',
            'kelas' => '12',
            'created_by' => $guru->id,
        ]);

        $material = Material::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Bab Awal',
            'description' => '<p>Deskripsi lama</p>',
            'created_by' => $guru->id,
        ]);

        $response = $this->actingAs($guru)->put(route('guru.materials.update', [$subject, $material]), [
            'title' => 'Bab Revisi',
            'description' => '<h2>Judul Baru</h2><p><em>Konten baru</em></p>',
            'file' => UploadedFile::fake()->create('revisi.pdf', 120, 'application/pdf'),
        ]);

        $response->assertRedirect(route('materials.show', [$subject, $material]));
        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'title' => 'Bab Revisi',
        ]);
    }

    public function test_guru_can_delete_material(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Geografi',
            'kelas' => '10',
            'created_by' => $guru->id,
        ]);

        $material = Material::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Bab Hapus',
            'description' => '<p>akan dihapus</p>',
            'created_by' => $guru->id,
        ]);

        $response = $this->actingAs($guru)->delete(route('guru.materials.destroy', [$subject, $material]));

        $response->assertRedirect(route('subjects.show', $subject));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('materials', [
            'id' => $material->id,
        ]);
    }
}
