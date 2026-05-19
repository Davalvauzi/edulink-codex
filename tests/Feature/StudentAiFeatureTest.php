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

    public function test_siswa_can_access_ai_payment_page_and_confirm_payment(): void
    {
        [$siswa] = $this->buildLearningContext();

        $paymentPage = $this->actingAs($siswa)->get(route('siswa.ai.payment'));

        $paymentPage->assertOk();
        $paymentPage->assertSee('Pembayaran AI Tutor');
        $paymentPage->assertSee('Bayar dengan QR');

        $confirmation = $this->actingAs($siswa)->post(route('siswa.ai.payment.confirm'));

        $confirmation->assertRedirect(route('siswa.ai.index'));
        $confirmation->assertSessionHas('success');

        $this->assertNotNull($siswa->fresh()->ai_tutor_paid_at);
    }

    public function test_tanya_ai_for_quiz_is_hidden_and_blocked_before_student_submits_answers(): void
    {
        [$siswa, $subject, $material] = $this->buildLearningContext();

        $quiz = Quiz::query()->create([
            'material_id' => $material->id,
            'title' => 'Kuis GLBB',
            'description' => 'Latihan konsep dasar.',
        ]);

        QuizQuestion::query()->create([
            'quiz_id' => $quiz->id,
            'question' => 'Apa satuan SI untuk percepatan?',
            'option_a' => 'm',
            'option_b' => 'm/s',
            'option_c' => 'm/s2',
            'option_d' => 'kg',
            'correct_option' => 'c',
            'position' => 1,
        ]);

        $quizPage = $this->actingAs($siswa)->get(route('quizzes.show', [$subject, $material, $quiz]));

        $quizPage->assertOk();
        $quizPage->assertDontSee('Tanya AI');

        $aiPage = $this->actingAs($siswa)->get(route('siswa.ai.index', [
            'subject' => $subject->id,
            'material' => $material->id,
            'quiz' => $quiz->id,
        ]));

        $aiPage->assertRedirect(route('quizzes.show', [$subject, $material, $quiz]));
        $aiPage->assertSessionHas('error');

        $this->assertDatabaseMissing('ai_conversations', [
            'user_id' => $siswa->id,
            'quiz_id' => $quiz->id,
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

    public function test_siswa_can_reset_ai_history_for_current_context_only(): void
    {
        [$siswa, $subject, $material, $subsection] = $this->buildLearningContext();
        $otherSiswa = User::factory()->create([
            'role' => 'siswa',
            'kelas' => User::GENERAL_KELAS,
        ]);

        $this->actingAs($siswa)->get(route('siswa.ai.index', [
            'subject' => $subject->id,
            'material' => $material->id,
            'subsection' => $subsection->id,
        ]));

        $conversation = AiConversation::query()->where('user_id', $siswa->id)->firstOrFail();
        $conversation->messages()->create([
            'role' => 'user',
            'content' => 'Tolong ringkas sub bab ini.',
        ]);

        $otherConversation = AiConversation::query()->create([
            'user_id' => $otherSiswa->id,
            'subject_id' => $subject->id,
            'material_id' => $material->id,
            'material_subsection_id' => $subsection->id,
            'context_hash' => $conversation->context_hash,
            'title' => 'Diskusi siswa lain',
        ]);

        $otherConversation->messages()->create([
            'role' => 'user',
            'content' => 'Riwayat siswa lain.',
        ]);

        $response = $this->actingAs($siswa)->delete(route('siswa.ai.destroy'), [
            'subject' => $subject->id,
            'material' => $material->id,
            'subsection' => $subsection->id,
        ]);

        $response->assertRedirect(route('siswa.ai.index', [
            'subject' => $subject->id,
            'material' => $material->id,
            'subsection' => $subsection->id,
        ]));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('ai_conversations', ['id' => $conversation->id]);
        $this->assertDatabaseMissing('ai_messages', ['ai_conversation_id' => $conversation->id]);
        $this->assertDatabaseHas('ai_conversations', ['id' => $otherConversation->id]);
        $this->assertDatabaseHas('ai_messages', ['ai_conversation_id' => $otherConversation->id]);
    }

    public function test_siswa_can_reset_own_quiz_answers_and_related_ai_history(): void
    {
        [$siswa, $subject, $material, $subsection, $quiz, $attempt] = $this->buildLearningContext(withAttempt: true);
        $otherSiswa = User::factory()->create([
            'role' => 'siswa',
            'kelas' => User::GENERAL_KELAS,
        ]);
        $question = QuizQuestion::query()->where('quiz_id', $quiz->id)->firstOrFail();

        $otherAttempt = QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $otherSiswa->id,
            'score' => 100,
            'correct_answers' => 1,
            'total_questions' => 1,
            'submitted_at' => now(),
        ]);

        QuizAttemptAnswer::query()->create([
            'quiz_attempt_id' => $otherAttempt->id,
            'quiz_question_id' => $question->id,
            'selected_option' => 'c',
            'is_correct' => true,
        ]);

        $conversation = AiConversation::query()->create([
            'user_id' => $siswa->id,
            'subject_id' => $subject->id,
            'material_id' => $material->id,
            'material_subsection_id' => $subsection->id,
            'quiz_id' => $quiz->id,
            'quiz_attempt_id' => $attempt->id,
            'context_hash' => 'subject='.$subject->id.':material='.$material->id.':subsection='.$subsection->id.':quiz='.$quiz->id.':attempt='.$attempt->id,
            'title' => 'Bahas hasil kuis',
        ]);

        $conversation->messages()->create([
            'role' => 'user',
            'content' => 'Kenapa jawaban saya salah?',
        ]);

        $otherConversation = AiConversation::query()->create([
            'user_id' => $otherSiswa->id,
            'subject_id' => $subject->id,
            'material_id' => $material->id,
            'quiz_id' => $quiz->id,
            'quiz_attempt_id' => $otherAttempt->id,
            'context_hash' => 'subject='.$subject->id.':material='.$material->id.':subsection=none:quiz='.$quiz->id.':attempt='.$otherAttempt->id,
            'title' => 'Bahas hasil kuis siswa lain',
        ]);

        $response = $this->actingAs($siswa)->delete(route('siswa.quizzes.answers.destroy', [$subject, $material, $quiz]));

        $response->assertRedirect(route('quizzes.show', [$subject, $material, $quiz]));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('quiz_attempts', ['id' => $attempt->id]);
        $this->assertDatabaseMissing('quiz_attempt_answers', ['quiz_attempt_id' => $attempt->id]);
        $this->assertDatabaseMissing('ai_conversations', ['id' => $conversation->id]);
        $this->assertDatabaseMissing('ai_messages', ['ai_conversation_id' => $conversation->id]);
        $this->assertDatabaseHas('quiz_attempts', ['id' => $otherAttempt->id]);
        $this->assertDatabaseHas('quiz_attempt_answers', ['quiz_attempt_id' => $otherAttempt->id]);
        $this->assertDatabaseHas('ai_conversations', ['id' => $otherConversation->id]);
    }

    private function buildLearningContext(bool $withAttempt = false): array
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
