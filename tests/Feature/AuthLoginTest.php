<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialSubsection;
use App\Models\Quiz;
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

    public function test_guru_can_add_subsection_to_existing_material(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Matematika',
            'kelas' => '10',
            'created_by' => $guru->id,
        ]);

        $material = Material::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Bab 1 Aljabar',
            'description' => '<p>Pengantar bab utama</p>',
            'created_by' => $guru->id,
        ]);

        $response = $this->actingAs($guru)->post(route('guru.materials.subsections.store', [$subject, $material]), [
            'title' => 'Bentuk Aljabar',
            'position' => 1,
            'description' => '<p>Isi sub bab bentuk aljabar</p>',
        ]);

        $response->assertRedirect(route('materials.show', [$subject, $material]));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('material_subsections', [
            'material_id' => $material->id,
            'title' => 'Bentuk Aljabar',
            'position' => 1,
            'created_by' => $guru->id,
        ]);
    }

    public function test_siswa_reading_subsection_updates_material_progress(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $siswa = User::factory()->create([
            'role' => 'siswa',
            'kelas' => '11',
        ]);

        $subject = Subject::query()->create([
            'name' => 'Matematika',
            'kelas' => '11',
            'created_by' => $guru->id,
        ]);

        $material = Material::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Bab 1 Aljabar',
            'description' => '<p>Bab utama aljabar</p>',
            'created_by' => $guru->id,
        ]);

        $subsectionOne = MaterialSubsection::query()->create([
            'material_id' => $material->id,
            'title' => 'Bentuk Aljabar',
            'description' => '<p>Sub bab pertama</p>',
            'position' => 1,
            'created_by' => $guru->id,
        ]);

        MaterialSubsection::query()->create([
            'material_id' => $material->id,
            'title' => 'Operasi Aljabar',
            'description' => '<p>Sub bab kedua</p>',
            'position' => 2,
            'created_by' => $guru->id,
        ]);

        $response = $this->actingAs($siswa)->get(route('materials.subsections.show', [$subject, $material, $subsectionOne]));

        $response->assertOk();
        $response->assertSee('1/2 sub bab selesai');
        $response->assertSee('50%');
        $this->assertDatabaseHas('material_subsection_progress', [
            'material_subsection_id' => $subsectionOne->id,
            'user_id' => $siswa->id,
        ]);

        $materialPageResponse = $this->actingAs($siswa)->get(route('materials.show', [$subject, $material]));

        $materialPageResponse->assertOk();
        $materialPageResponse->assertSee('1 dari 2 sub bab');
        $materialPageResponse->assertSee('50%');
        $materialPageResponse->assertSee('Sudah Dibaca');
    }

    public function test_guru_can_create_multiple_choice_quiz_for_material(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Biologi',
            'kelas' => '11',
            'created_by' => $guru->id,
        ]);

        $material = Material::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Bab Sistem Pernapasan',
            'description' => '<p>Materi sistem pernapasan</p>',
            'created_by' => $guru->id,
        ]);

        $response = $this->actingAs($guru)->post(route('guru.materials.quizzes.store', [$subject, $material]), [
            'title' => 'Latihan Sistem Pernapasan',
            'description' => 'Kerjakan semua soal berikut.',
            'questions' => [
                [
                    'question' => 'Organ utama pernapasan manusia adalah?',
                    'option_a' => 'Jantung',
                    'option_b' => 'Paru-paru',
                    'option_c' => 'Ginjal',
                    'option_d' => 'Lambung',
                    'correct_option' => 'b',
                    'image_url' => 'https://example.com/paru-paru.jpg',
                    'explanation' => 'Paru-paru berfungsi sebagai tempat pertukaran oksigen dan karbon dioksida.',
                ],
            ],
        ]);

        $quiz = Quiz::query()->where('material_id', $material->id)->firstOrFail();

        $response->assertRedirect(route('quizzes.show', [$subject, $material, $quiz]));
        $this->assertDatabaseHas('quizzes', [
            'material_id' => $material->id,
            'title' => 'Latihan Sistem Pernapasan',
            'created_by' => $guru->id,
        ]);
        $this->assertDatabaseHas('quiz_questions', [
            'quiz_id' => $quiz->id,
            'correct_option' => 'b',
            'image_url' => 'https://example.com/paru-paru.jpg',
        ]);
    }

    public function test_siswa_can_submit_quiz_and_see_score_with_explanation_for_wrong_answers(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $siswa = User::factory()->create([
            'role' => 'siswa',
            'kelas' => '10',
        ]);

        $subject = Subject::query()->create([
            'name' => 'Matematika',
            'kelas' => '10',
            'created_by' => $guru->id,
        ]);

        $material = Material::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Bab Pecahan',
            'description' => '<p>Materi pecahan</p>',
            'created_by' => $guru->id,
        ]);

        $quiz = $material->quizzes()->create([
            'title' => 'Latihan Pecahan',
            'description' => 'Pilih jawaban yang tepat.',
            'created_by' => $guru->id,
        ]);

        $firstQuestion = $quiz->questions()->create([
            'question' => 'Hasil 1/2 + 1/2 adalah?',
            'option_a' => '1',
            'option_b' => '2',
            'option_c' => '1/2',
            'option_d' => '0',
            'correct_option' => 'a',
            'explanation' => 'Dua pecahan setengah jika dijumlahkan sama dengan satu utuh.',
            'position' => 1,
        ]);

        $secondQuestion = $quiz->questions()->create([
            'question' => 'Hasil 3/4 - 1/4 adalah?',
            'option_a' => '1',
            'option_b' => '1/4',
            'option_c' => '1/2',
            'option_d' => '3/4',
            'correct_option' => 'c',
            'explanation' => 'Karena pembilangnya 3 dikurangi 1 menjadi 2, sehingga hasilnya 2/4 atau 1/2.',
            'position' => 2,
        ]);

        $response = $this->actingAs($siswa)->post(route('quizzes.submit', [$subject, $material, $quiz]), [
            'answers' => [
                $firstQuestion->id => 'a',
                $secondQuestion->id => 'a',
            ],
        ]);

        $response->assertRedirect(route('quizzes.show', [$subject, $material, $quiz]));

        $showResponse = $this->actingAs($siswa)->get(route('quizzes.show', [$subject, $material, $quiz]));

        $showResponse->assertOk();
        $showResponse->assertSee('Skor Terakhir');
        $showResponse->assertSee('50');
        $showResponse->assertSee('Review Jawaban Salah');
        $showResponse->assertSee('Jawaban Benar: C');
        $showResponse->assertSee('hasilnya 2/4 atau 1/2.', false);

        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id' => $quiz->id,
            'user_id' => $siswa->id,
            'score' => 50,
            'correct_answers' => 1,
            'total_questions' => 2,
        ]);
        $this->assertDatabaseHas('quiz_attempt_answers', [
            'quiz_question_id' => $secondQuestion->id,
            'selected_option' => 'a',
            'is_correct' => false,
        ]);
    }
}
