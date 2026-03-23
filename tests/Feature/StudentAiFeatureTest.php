<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\Material;
use App\Models\MaterialSubsection;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAiFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_can_open_tanya_ai_page_from_material_context(): void
    {
        [$siswa, $subject, $material, $subsection] = $this->buildLearningContext();

        $response = $this->actingAs($siswa)->get(route('siswa.ai.index', [
            'subject' => $subject->id,
            'material' => $material->id,
            'subsection' => $subsection->id,
        ]));

        $response->assertOk();
        $response->assertSee('Tanya AI');
        $response->assertSee($material->title);
        $response->assertSee($subsection->title);

        $this->assertDatabaseHas('ai_conversations', [
            'user_id' => $siswa->id,
            'subject_id' => $subject->id,
            'material_id' => $material->id,
            'material_subsection_id' => $subsection->id,
        ]);
    }

    public function test_siswa_gets_clear_error_when_groq_key_is_missing(): void
    {
        config()->set('services.groq.api_key', '');

        [$siswa, $subject, $material, $subsection, $quiz, $attempt] = $this->buildLearningContext(withAttempt: true);

        $response = $this->actingAs($siswa)->post(route('siswa.ai.store'), [
            'subject' => $subject->id,
            'material' => $material->id,
            'subsection' => $subsection->id,
            'quiz' => $quiz->id,
            'attempt' => $attempt->id,
            'message' => 'Kenapa jawaban saya salah?',
        ]);

        $response->assertRedirect(route('siswa.ai.index', [
            'subject' => $subject->id,
            'material' => $material->id,
            'subsection' => $subsection->id,
            'quiz' => $quiz->id,
            'attempt' => $attempt->id,
        ]));
        $response->assertSessionHas('error');

        $conversation = AiConversation::query()->firstOrFail();

        $this->assertDatabaseHas('ai_messages', [
            'ai_conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Kenapa jawaban saya salah?',
        ]);
        $this->assertDatabaseMissing('ai_messages', [
            'ai_conversation_id' => $conversation->id,
            'role' => 'assistant',
        ]);
    }

    private function buildLearningContext(bool $withAttempt = false): array
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
            'name' => 'Fisika',
            'kelas' => '11',
            'created_by' => $guru->id,
        ]);

        $material = Material::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Gerak Lurus',
            'description' => '<p>Materi tentang gerak lurus berubah beraturan.</p>',
            'created_by' => $guru->id,
        ]);

        $subsection = MaterialSubsection::query()->create([
            'material_id' => $material->id,
            'title' => 'Kecepatan dan Percepatan',
            'description' => '<p>Sub bab membahas rumus dasar.</p>',
            'position' => 1,
            'created_by' => $guru->id,
        ]);

        if (! $withAttempt) {
            return [$siswa, $subject, $material, $subsection];
        }

        $quiz = Quiz::query()->create([
            'material_id' => $material->id,
            'title' => 'Kuis GLBB',
            'description' => 'Latihan konsep dasar.',
            'created_by' => $guru->id,
        ]);

        $question = QuizQuestion::query()->create([
            'quiz_id' => $quiz->id,
            'question' => 'Apa satuan SI untuk percepatan?',
            'option_a' => 'm',
            'option_b' => 'm/s',
            'option_c' => 'm/s2',
            'option_d' => 'kg',
            'correct_option' => 'c',
            'explanation' => 'Percepatan adalah perubahan kecepatan per satuan waktu.',
            'position' => 1,
        ]);

        $attempt = QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $siswa->id,
            'score' => 0,
            'correct_answers' => 0,
            'total_questions' => 1,
            'submitted_at' => now(),
        ]);

        QuizAttemptAnswer::query()->create([
            'quiz_attempt_id' => $attempt->id,
            'quiz_question_id' => $question->id,
            'selected_option' => 'b',
            'is_correct' => false,
        ]);

        return [$siswa, $subject, $material, $subsection, $quiz, $attempt];
    }
}
