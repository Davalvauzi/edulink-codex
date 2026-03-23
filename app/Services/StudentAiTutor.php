<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Material;
use App\Models\MaterialSubsection;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class StudentAiTutor
{
    public function reply(AiConversation $conversation): array
    {
        $apiKey = (string) config('services.groq.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('Fitur Tanya AI belum aktif. Tambahkan GROQ_API_KEY di file .env terlebih dahulu.');
        }

        $conversation->loadMissing([
            'user',
            'subject',
            'material.subject',
            'subsection.material.subject',
            'quiz.material.subject',
            'quiz.questions',
            'quizAttempt.answers.question',
            'messages',
        ]);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout((int) config('services.groq.timeout', 30))
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.model', 'llama-3.3-70b-versatile'),
                'messages' => $this->buildMessages($conversation),
                'max_tokens' => 600,
                'temperature' => 0.3,
            ])
            ->throw()
            ->json();

        $content = trim((string) data_get($response, 'choices.0.message.content', ''));

        if ($content === '') {
            throw new RuntimeException('AI belum mengembalikan jawaban. Coba kirim pertanyaan sekali lagi.');
        }

        return [
            'content' => $content,
            'response_id' => $response['id'] ?? null,
            'prompt_tokens' => data_get($response, 'usage.prompt_tokens'),
            'completion_tokens' => data_get($response, 'usage.completion_tokens'),
            'total_tokens' => data_get($response, 'usage.total_tokens'),
        ];
    }

    public function buildPageContext(User $user, ?Material $material = null, ?MaterialSubsection $subsection = null, ?Quiz $quiz = null, ?QuizAttempt $quizAttempt = null): array
    {
        $recentAttempts = QuizAttempt::query()
            ->with(['quiz.material.subject', 'answers.question'])
            ->where('user_id', $user->id)
            ->latest('submitted_at')
            ->latest('id')
            ->take(5)
            ->get();

        $wrongAnswers = $recentAttempts
            ->flatMap(fn (QuizAttempt $attempt) => $attempt->answers
                ->where('is_correct', false)
                ->map(fn (QuizAttemptAnswer $answer) => [
                    'quiz' => $attempt->quiz?->title ?? 'Kuis',
                    'material' => $attempt->quiz?->material?->title ?? '-',
                    'question' => $answer->question?->question ?? '-',
                    'selected_option' => strtoupper($answer->selected_option),
                    'correct_option' => strtoupper((string) $answer->question?->correct_option),
                    'explanation' => $answer->question?->explanation,
                ]))
            ->take(6)
            ->values();

        return [
            'contextTitle' => $this->buildTitle($material, $subsection, $quiz, $quizAttempt),
            'contextDescription' => $this->buildDescription($material, $subsection, $quiz, $quizAttempt),
            'wrongAnswers' => $wrongAnswers,
            'recentAttempts' => $recentAttempts,
        ];
    }

    public function buildConversationTitle(?Material $material = null, ?MaterialSubsection $subsection = null, ?Quiz $quiz = null, ?QuizAttempt $quizAttempt = null): string
    {
        return $this->buildTitle($material, $subsection, $quiz, $quizAttempt);
    }

    private function buildMessages(AiConversation $conversation): array
    {
        $history = $conversation->messages
            ->take(-10)
            ->map(fn (AiMessage $message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->values()
            ->all();

        array_unshift($history, [
            'role' => 'system',
            'content' => $this->buildDeveloperPrompt($conversation),
        ]);

        return $history;
    }

    private function buildDeveloperPrompt(AiConversation $conversation): string
    {
        $user = $conversation->user;
        $material = $conversation->material;
        $subsection = $conversation->subsection;
        $quiz = $conversation->quiz;
        $quizAttempt = $conversation->quizAttempt;

        $lines = [
            'Anda adalah tutor AI untuk siswa Indonesia di aplikasi EduLink.',
            'Tujuan Anda membantu siswa memahami materi pelajaran dengan bahasa sederhana, bertahap, dan suportif.',
            'Berikan jawaban dalam Bahasa Indonesia.',
            'Fokus pada penjelasan konsep, koreksi miskonsepsi, dan langkah belajar berikutnya.',
            'Jika pertanyaan siswa terkait jawaban kuis yang salah, jelaskan kenapa salah dan bagaimana menemukan jawaban yang benar tanpa sekadar menyebut kunci.',
            'Jangan mengaku melihat data yang tidak ada. Jika konteks kurang, katakan dengan jujur lalu tetap bantu berdasarkan informasi yang tersedia.',
            'Jawaban maksimal 220 kata, ringkas, jelas, dan boleh pakai bullet singkat bila membantu.',
            'Ajak siswa membuka materi atau sub bab terkait jika itu akan membantu.',
            '',
            'Profil siswa:',
            '- Nama: '.$user->name,
            '- Kelas: '.($user->kelas ?: '-'),
        ];

        if ($conversation->subject) {
            $lines[] = '- Mata pelajaran: '.$conversation->subject->name;
        }

        if ($material) {
            $lines[] = '';
            $lines[] = 'Bab yang sedang dipelajari:';
            $lines[] = '- Judul: '.$material->title;
            $lines[] = '- Ringkasan: '.$this->cleanText($material->description, 1200);
            $lines[] = '- Daftar sub bab: '.$material->subsections()
                ->orderBy('position')
                ->get()
                ->map(fn (MaterialSubsection $item) => $item->position.'. '.$item->title)
                ->implode('; ');
        }

        if ($subsection) {
            $lines[] = '';
            $lines[] = 'Sub bab aktif:';
            $lines[] = '- Judul: '.$subsection->title;
            $lines[] = '- Isi ringkas: '.$this->cleanText($subsection->description, 1600);
        }

        if ($quiz) {
            $lines[] = '';
            $lines[] = 'Kuis terkait:';
            $lines[] = '- Judul: '.$quiz->title;
            $lines[] = '- Deskripsi: '.($quiz->description ?: 'Tidak ada deskripsi tambahan.');
            $lines[] = '- Daftar soal: '.$quiz->questions
                ->map(fn ($question) => 'Soal '.$question->position.': '.$this->cleanText($question->question, 220))
                ->implode(' | ');
        }

        $wrongAnswerSummary = $this->buildWrongAnswerSummary($conversation);

        if ($wrongAnswerSummary !== '') {
            $lines[] = '';
            $lines[] = 'Riwayat kesalahan siswa yang relevan:';
            $lines[] = $wrongAnswerSummary;
        }

        return implode("\n", $lines);
    }

    private function buildWrongAnswerSummary(AiConversation $conversation): string
    {
        $attempts = collect();

        if ($conversation->quizAttempt) {
            $attempts = collect([$conversation->quizAttempt]);
        } elseif ($conversation->quiz) {
            $attempt = QuizAttempt::query()
                ->with(['answers.question', 'quiz.material.subject'])
                ->where('quiz_id', $conversation->quiz->id)
                ->where('user_id', $conversation->user_id)
                ->latest('submitted_at')
                ->latest('id')
                ->first();

            $attempts = $attempt ? collect([$attempt]) : collect();
        } elseif ($conversation->material) {
            $attempts = QuizAttempt::query()
                ->with(['answers.question', 'quiz.material.subject'])
                ->where('user_id', $conversation->user_id)
                ->whereHas('quiz', fn ($query) => $query->where('material_id', $conversation->material->id))
                ->latest('submitted_at')
                ->latest('id')
                ->take(3)
                ->get();
        } else {
            $attempts = QuizAttempt::query()
                ->with(['answers.question', 'quiz.material.subject'])
                ->where('user_id', $conversation->user_id)
                ->latest('submitted_at')
                ->latest('id')
                ->take(3)
                ->get();
        }

        return $attempts
            ->flatMap(function (QuizAttempt $attempt) {
                return $attempt->answers
                    ->where('is_correct', false)
                    ->map(function (QuizAttemptAnswer $answer) use ($attempt) {
                        $question = $answer->question;

                        if (! $question) {
                            return null;
                        }

                        return '- '.$attempt->quiz?->title.' | Soal '.$question->position
                            .': '.$this->cleanText($question->question, 220)
                            .' | Jawaban siswa: '.strtoupper($answer->selected_option)
                            .' | Kunci: '.strtoupper($question->correct_option)
                            .' | Pembahasan guru: '.($question->explanation ?: 'Belum ada pembahasan tambahan.');
                    });
            })
            ->filter()
            ->take(6)
            ->implode("\n");
    }

    private function buildTitle(?Material $material = null, ?MaterialSubsection $subsection = null, ?Quiz $quiz = null, ?QuizAttempt $quizAttempt = null): string
    {
        if ($quizAttempt) {
            return 'Bahas hasil kuis '.$quizAttempt->quiz?->title;
        }

        if ($quiz) {
            return 'Diskusi kuis '.$quiz->title;
        }

        if ($subsection) {
            return 'Konsultasi sub bab '.$subsection->title;
        }

        if ($material) {
            return 'Konsultasi materi '.$material->title;
        }

        return 'Tanya AI';
    }

    private function buildDescription(?Material $material = null, ?MaterialSubsection $subsection = null, ?Quiz $quiz = null, ?QuizAttempt $quizAttempt = null): string
    {
        if ($quizAttempt) {
            return 'AI akan memakai hasil attempt ini, terutama jawaban yang salah, untuk membantu membahas konsep yang belum pas.';
        }

        if ($quiz) {
            return 'AI akan membahas soal, konsep, dan kesalahan pengerjaan terbaru pada kuis ini bila tersedia.';
        }

        if ($subsection) {
            return 'AI akan fokus pada isi sub bab aktif dan menghubungkannya ke bab utama bila diperlukan.';
        }

        if ($material) {
            return 'AI akan memakai ringkasan bab, daftar sub bab, dan riwayat kuis terkait materi ini.';
        }

        return 'Ajukan pertanyaan tentang materi pembelajaran, konsep yang belum paham, atau minta bantuan memahami kesalahan saat kuis.';
    }

    private function cleanText(?string $value, int $limit): string
    {
        return Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)) ?? ''), $limit);
    }
}
