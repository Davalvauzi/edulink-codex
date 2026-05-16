<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\Material;
use App\Models\MaterialSubsection;
use App\Models\Quiz;
use App\Models\QuizAttempt;
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

    public function test_guest_can_register_as_siswa_with_default_kelas(): void
    {
        $response = $this->post('/register', [
            'name' => 'Siswa Baru',
            'email' => 'siswa-baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'siswa-baru@example.com',
            'role' => 'siswa',
            'kelas' => User::GENERAL_KELAS,
        ]);
    }

    public function test_siswa_can_update_profile_from_profile_page(): void
    {
        $user = User::factory()->create([
            'role' => 'siswa',
            'kelas' => User::GENERAL_KELAS,
            'email' => 'siswa-lama@example.com',
        ]);

        $response = $this->actingAs($user)->put('/siswa/profile', [
            'name' => 'Siswa Update',
            'email' => 'siswa-update@example.com',
            'kelas' => User::GENERAL_KELAS,
            'password' => 'password999',
            'password_confirmation' => 'password999',
        ]);

        $response->assertRedirect(route('siswa.profile'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Siswa Update',
            'email' => 'siswa-update@example.com',
            'kelas' => User::GENERAL_KELAS,
        ]);
    }

    public function test_siswa_dashboard_defaults_to_general_subjects(): void
    {
        $user = User::factory()->create([
            'role' => 'siswa',
            'kelas' => User::GENERAL_KELAS,
        ]);

        Subject::query()->create(['name' => 'Fisika Lanjut', 'kelas' => User::GENERAL_KELAS]);

        $response = $this->actingAs($user)->get('/siswa/dashboard');

        $response->assertOk();
        $response->assertSee('Mapel Kelas Umum');
        $response->assertSee('Menampilkan Kelas Umum');
    }

    public function test_siswa_dashboard_ignores_invalid_kelas_filter(): void
    {
        $user = User::factory()->create([
            'role' => 'siswa',
            'kelas' => User::GENERAL_KELAS,
        ]);

        Subject::query()->create(['name' => 'Sosiologi Global', 'kelas' => User::GENERAL_KELAS]);

        $response = $this->actingAs($user)->get('/siswa/dashboard?kelas=12');

        $response->assertOk();
        $response->assertSee('Mapel Kelas Umum');
        $response->assertSee('Menampilkan Kelas Umum');
    }

    public function test_guru_can_add_subject_for_specific_kelas(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $response = $this->actingAs($guru)->post(route('guru.subjects.store'), [
            'name' => 'Kimia',
            'kelas' => User::GENERAL_KELAS,
        ]);

        $subject = Subject::query()->where('name', 'Kimia')->firstOrFail();

        $response->assertRedirect(route('subjects.show', $subject));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('subjects', [
            'name' => 'Kimia',
            'kelas' => User::GENERAL_KELAS,
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
            'kelas' => User::GENERAL_KELAS,
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
            'kelas' => User::GENERAL_KELAS,
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
            'kelas' => User::GENERAL_KELAS,
            'created_by' => $guru->id,
        ]);

        $response = $this->actingAs($guru)->get(route('guru.subjects.materials.create', $subject));

        $response->assertOk();
        $response->assertSee('Tambah Materi Baru');
        $response->assertSee('Biologi');
    }

    public function test_guru_quizzes_page_shows_empty_state_when_no_quiz_exists(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $response = $this->actingAs($guru)->get(route('guru.quizzes'));

        $response->assertOk();
        $response->assertSee('Belum ada kuis yang tersedia saat ini.');
        $response->assertSee('Kembali ke Materi');
    }

    public function test_material_has_dedicated_detail_page(): void
    {
        $siswa = User::factory()->create([
            'role' => 'siswa',
            'kelas' => User::GENERAL_KELAS,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Sejarah',
            'kelas' => User::GENERAL_KELAS,
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

    public function test_material_menu_redirects_to_bahasa_inggris_subject(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $siswa = User::factory()->create([
            'role' => 'siswa',
            'kelas' => User::GENERAL_KELAS,
        ]);

        Subject::query()->create([
            'name' => 'Matematika',
            'kelas' => User::GENERAL_KELAS,
            'created_by' => $guru->id,
        ]);

        $englishSubject = Subject::query()->create([
            'name' => 'Bahasa Inggris',
            'kelas' => User::GENERAL_KELAS,
            'created_by' => $guru->id,
        ]);

        $this->actingAs($guru)
            ->get(route('guru.materials'))
            ->assertRedirect(route('subjects.show', $englishSubject));

        $this->actingAs($siswa)
            ->get(route('siswa.materials'))
            ->assertRedirect(route('subjects.show', $englishSubject));
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
            'kelas' => User::GENERAL_KELAS,
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
            'kelas' => User::GENERAL_KELAS,
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

    public function test_guru_can_delete_quiz_with_attempts_and_ai_history(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $siswa = User::factory()->create([
            'role' => 'siswa',
            'kelas' => User::GENERAL_KELAS,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Fisika',
            'kelas' => User::GENERAL_KELAS,
            'created_by' => $guru->id,
        ]);

        $material = Material::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Bab Energi',
            'description' => '<p>Materi energi</p>',
            'created_by' => $guru->id,
        ]);

        $quiz = $material->quizzes()->create([
            'title' => 'Kuis Energi',
            'description' => 'Latihan energi.',
            'created_by' => $guru->id,
        ]);

        $question = $quiz->questions()->create([
            'question' => 'Satuan energi adalah?',
            'option_a' => 'Joule',
            'option_b' => 'Meter',
            'option_c' => 'Sekon',
            'option_d' => 'Newton',
            'correct_option' => 'a',
            'position' => 1,
        ]);

        $attempt = $quiz->attempts()->create([
            'user_id' => $siswa->id,
            'score' => 0,
            'correct_answers' => 0,
            'total_questions' => 1,
            'submitted_at' => now(),
        ]);

        $attempt->answers()->create([
            'quiz_question_id' => $question->id,
            'selected_option' => 'b',
            'is_correct' => false,
        ]);

        $conversation = AiConversation::query()->create([
            'user_id' => $siswa->id,
            'subject_id' => $subject->id,
            'material_id' => $material->id,
            'quiz_id' => $quiz->id,
            'quiz_attempt_id' => $attempt->id,
            'context_hash' => 'quiz='.$quiz->id,
            'title' => 'Diskusi kuis',
        ]);

        $conversation->messages()->create([
            'role' => 'user',
            'content' => 'Kenapa salah?',
        ]);

        $response = $this->actingAs($guru)->delete(route('guru.materials.quizzes.destroy', [$subject, $material, $quiz]));

        $response->assertRedirect(route('materials.show', [$subject, $material]));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
        $this->assertDatabaseMissing('quiz_questions', ['id' => $question->id]);
        $this->assertDatabaseMissing('quiz_attempts', ['id' => $attempt->id]);
        $this->assertDatabaseMissing('ai_conversations', ['id' => $conversation->id]);
        $this->assertDatabaseMissing('ai_messages', ['ai_conversation_id' => $conversation->id]);
    }

    public function test_guru_can_delete_material_ai_history_without_deleting_quizzes(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $siswa = User::factory()->create([
            'role' => 'siswa',
            'kelas' => User::GENERAL_KELAS,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Kimia',
            'kelas' => User::GENERAL_KELAS,
            'created_by' => $guru->id,
        ]);

        $material = Material::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Bab Atom',
            'description' => '<p>Materi atom</p>',
            'created_by' => $guru->id,
        ]);

        $quiz = $material->quizzes()->create([
            'title' => 'Kuis Atom',
            'created_by' => $guru->id,
        ]);

        $conversation = AiConversation::query()->create([
            'user_id' => $siswa->id,
            'subject_id' => $subject->id,
            'material_id' => $material->id,
            'context_hash' => 'material='.$material->id,
            'title' => 'Diskusi materi',
        ]);

        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => 'Mari bahas atom.',
        ]);

        $response = $this->actingAs($guru)->delete(route('guru.materials.ai-history.destroy', [$subject, $material]));

        $response->assertRedirect(route('materials.show', [$subject, $material]));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id]);
        $this->assertDatabaseMissing('ai_conversations', ['id' => $conversation->id]);
        $this->assertDatabaseMissing('ai_messages', ['ai_conversation_id' => $conversation->id]);
    }

    public function test_guru_can_add_subsection_to_existing_material(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Matematika',
            'kelas' => User::GENERAL_KELAS,
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
            'image_url' => 'https://example.com/sub-bab.jpg',
        ]);

        $response->assertRedirect(route('materials.show', [$subject, $material]));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('material_subsections', [
            'material_id' => $material->id,
            'title' => 'Bentuk Aljabar',
            'position' => 1,
            'created_by' => $guru->id,
            'image_url' => 'https://example.com/sub-bab.jpg',
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
            'kelas' => User::GENERAL_KELAS,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Matematika',
            'kelas' => User::GENERAL_KELAS,
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
        $materialPageResponse->assertDontSee('Daftar Sub Bab');
        $materialPageResponse->assertDontSee('Sudah Dibaca');
    }

    public function test_subsection_detail_shows_next_button_and_supports_image(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $siswa = User::factory()->create([
            'role' => 'siswa',
            'kelas' => User::GENERAL_KELAS,
        ]);

        $subject = Subject::query()->create([
            'name' => 'IPA',
            'kelas' => User::GENERAL_KELAS,
            'created_by' => $guru->id,
        ]);

        $material = Material::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Bab Ekosistem',
            'description' => '<p>Materi ekosistem</p>',
            'created_by' => $guru->id,
        ]);

        $currentSubsection = MaterialSubsection::query()->create([
            'material_id' => $material->id,
            'title' => 'Pengertian Ekosistem',
            'description' => '<p>Isi sub bab pertama</p>',
            'image_url' => 'https://example.com/ekosistem.jpg',
            'position' => 1,
            'created_by' => $guru->id,
        ]);

        MaterialSubsection::query()->create([
            'material_id' => $material->id,
            'title' => 'Komponen Ekosistem',
            'description' => '<p>Isi sub bab kedua</p>',
            'position' => 2,
            'created_by' => $guru->id,
        ]);

        $response = $this->actingAs($siswa)->get(route('materials.subsections.show', [$subject, $material, $currentSubsection]));

        $response->assertOk();
        $response->assertSee('Isi Sub Bab');
        $response->assertSee('Selanjutnya');
        $response->assertSee('https://example.com/ekosistem.jpg');
    }

    public function test_guru_can_create_multiple_choice_quiz_for_material(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Biologi',
            'kelas' => User::GENERAL_KELAS,
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
            'kelas' => User::GENERAL_KELAS,
        ]);

        $subject = Subject::query()->create([
            'name' => 'Matematika',
            'kelas' => User::GENERAL_KELAS,
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
        $showResponse->assertSee('Hasil Pengerjaan');
        $showResponse->assertSee('Jawaban benar: C');
        $showResponse->assertSee('hasilnya 2/4 atau 1/2.', false);
        $showResponse->assertSee('Print PDF');

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

    public function test_guru_can_view_all_student_quiz_results_on_quiz_page(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $firstSiswa = User::factory()->create([
            'role' => 'siswa',
            'kelas' => User::GENERAL_KELAS,
            'name' => 'Siswa Pertama',
            'email' => 'pertama@example.com',
        ]);

        $secondSiswa = User::factory()->create([
            'role' => 'siswa',
            'kelas' => User::GENERAL_KELAS,
            'name' => 'Siswa Kedua',
            'email' => 'kedua@example.com',
        ]);

        $subject = Subject::query()->create([
            'name' => 'Matematika',
            'kelas' => User::GENERAL_KELAS,
            'created_by' => $guru->id,
        ]);

        $material = Material::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Bab Persamaan',
            'description' => '<p>Materi persamaan linear</p>',
            'created_by' => $guru->id,
        ]);

        $quiz = $material->quizzes()->create([
            'title' => 'Kuis Persamaan',
            'created_by' => $guru->id,
        ]);

        $question = $quiz->questions()->create([
            'question' => 'Nilai x dari x + 2 = 5 adalah?',
            'option_a' => '2',
            'option_b' => '3',
            'option_c' => '5',
            'option_d' => '7',
            'correct_option' => 'b',
            'explanation' => 'Kurangi kedua ruas dengan 2.',
            'position' => 1,
        ]);

        $firstAttempt = $quiz->attempts()->create([
            'user_id' => $firstSiswa->id,
            'score' => 100,
            'correct_answers' => 1,
            'total_questions' => 1,
            'submitted_at' => now()->subMinute(),
        ]);

        $firstAttempt->answers()->create([
            'quiz_question_id' => $question->id,
            'selected_option' => 'b',
            'is_correct' => true,
        ]);

        $secondAttempt = $quiz->attempts()->create([
            'user_id' => $secondSiswa->id,
            'score' => 0,
            'correct_answers' => 0,
            'total_questions' => 1,
            'submitted_at' => now(),
        ]);

        $secondAttempt->answers()->create([
            'quiz_question_id' => $question->id,
            'selected_option' => 'a',
            'is_correct' => false,
        ]);

        $response = $this->actingAs($guru)->get(route('quizzes.show', [$subject, $material, $quiz]));

        $response->assertOk();
        $response->assertSee('Result Kuis Siswa');
        $response->assertSee('Siswa Pertama');
        $response->assertSee('pertama@example.com');
        $response->assertSee('Skor 100');
        $response->assertSee('Siswa Kedua');
        $response->assertSee('kedua@example.com');
        $response->assertSee('Skor 0');
        $response->assertSee('Jawaban siswa: A');
        $response->assertSee('Kunci: B');
        $response->assertSee('Print PDF');
    }

    public function test_siswa_can_open_printable_quiz_result_page(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'kelas' => null,
        ]);

        $siswa = User::factory()->create([
            'role' => 'siswa',
            'kelas' => User::GENERAL_KELAS,
            'name' => 'Siswa Cetak',
            'email' => 'cetak@example.com',
        ]);

        $subject = Subject::query()->create([
            'name' => 'Bahasa Inggris',
            'kelas' => User::GENERAL_KELAS,
            'created_by' => $guru->id,
        ]);

        $material = Material::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Bab Reading',
            'description' => '<p>Materi reading</p>',
            'created_by' => $guru->id,
        ]);

        $quiz = $material->quizzes()->create([
            'title' => 'Kuis Reading',
            'description' => 'Jawab dengan teliti.',
            'created_by' => $guru->id,
        ]);

        $question = $quiz->questions()->create([
            'question' => 'Choose the correct answer.',
            'option_a' => 'A',
            'option_b' => 'B',
            'option_c' => 'C',
            'option_d' => 'D',
            'correct_option' => 'b',
            'explanation' => 'Pilihan yang benar adalah B.',
            'position' => 1,
        ]);

        $this->actingAs($siswa)->post(route('quizzes.submit', [$subject, $material, $quiz]), [
            'answers' => [
                $question->id => 'a',
            ],
        ]);

        $attempt = QuizAttempt::query()->where('quiz_id', $quiz->id)->where('user_id', $siswa->id)->firstOrFail();

        $response = $this->actingAs($siswa)->get(route('quizzes.attempts.print', [$subject, $material, $quiz, $attempt]));

        $response->assertOk();
        $response->assertSee('Hasil Kuis');
        $response->assertSee('Siswa Cetak');
        $response->assertSee('Jawaban siswa: A');
        $response->assertSee('Jawaban benar: B');
    }
}
