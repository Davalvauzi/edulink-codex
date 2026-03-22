<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function create(Request $request, Subject $subject, Material $material): View
    {
        abort_if($request->user()->role !== 'guru', 403);
        $this->ensureMaterialBelongsToSubject($subject, $material);

        return view('quizzes.create', [
            'title' => 'Buat Kuis',
            'role' => $request->user()->role,
            'user' => $request->user(),
            'subject' => $subject,
            'material' => $material,
        ]);
    }

    public function store(Request $request, Subject $subject, Material $material): RedirectResponse
    {
        abort_if($request->user()->role !== 'guru', 403);
        $this->ensureMaterialBelongsToSubject($subject, $material);

        $data = $this->validateQuiz($request);

        $quiz = DB::transaction(function () use ($request, $material, $data) {
            $quiz = $material->quizzes()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            $position = 1;

            foreach ($data['questions'] as $index => $question) {
                [$imagePath, $imageName] = $this->storeQuestionImage($request, $index);

                $quiz->questions()->create([
                    'question' => $question['question'],
                    'option_a' => $question['option_a'],
                    'option_b' => $question['option_b'],
                    'option_c' => $question['option_c'],
                    'option_d' => $question['option_d'],
                    'correct_option' => $question['correct_option'],
                    'explanation' => $question['explanation'] ?? null,
                    'image_path' => $imagePath,
                    'image_name' => $imageName,
                    'image_url' => $question['image_url'] ?? null,
                    'position' => $position,
                ]);

                $position++;
            }

            return $quiz;
        });

        return redirect()
            ->route('quizzes.show', [$subject, $material, $quiz])
            ->with('success', 'Kuis berhasil dibuat dan siap dikerjakan siswa.');
    }

    public function show(Request $request, Subject $subject, Material $material, Quiz $quiz): View
    {
        $this->ensureMaterialBelongsToSubject($subject, $material);
        $this->ensureQuizBelongsToMaterial($material, $quiz);

        $user = $request->user();
        abort_if(! in_array($user->role, ['guru', 'siswa'], true), 403);

        $quiz->load(['creator', 'questions', 'material.subject']);

        $latestAttempt = null;

        if ($user->role === 'siswa') {
            $latestAttempt = QuizAttempt::query()
                ->with(['answers.question'])
                ->where('quiz_id', $quiz->id)
                ->where('user_id', $user->id)
                ->latest('submitted_at')
                ->latest('id')
                ->first();
        }

        return view('quizzes.show', [
            'title' => $quiz->title,
            'role' => $user->role,
            'user' => $user,
            'subject' => $subject,
            'material' => $material,
            'quiz' => $quiz,
            'latestAttempt' => $latestAttempt,
        ]);
    }

    public function submit(Request $request, Subject $subject, Material $material, Quiz $quiz): RedirectResponse
    {
        abort_if($request->user()->role !== 'siswa', 403);
        $this->ensureMaterialBelongsToSubject($subject, $material);
        $this->ensureQuizBelongsToMaterial($material, $quiz);

        $quiz->load('questions');

        $rules = ['answers' => ['required', 'array']];
        foreach ($quiz->questions as $question) {
            $rules['answers.'.$question->id] = ['required', 'in:a,b,c,d'];
        }

        $validated = $request->validate($rules, [
            'answers.required' => 'Semua soal harus dijawab sebelum kuis dikirim.',
            'answers.*.required' => 'Semua soal harus dijawab sebelum kuis dikirim.',
        ]);

        $attempt = DB::transaction(function () use ($request, $quiz, $validated) {
            $questions = $quiz->questions;
            $correctAnswers = 0;

            $attempt = $quiz->attempts()->create([
                'user_id' => $request->user()->id,
                'score' => 0,
                'correct_answers' => 0,
                'total_questions' => $questions->count(),
                'submitted_at' => Carbon::now(),
            ]);

            foreach ($questions as $question) {
                $selectedOption = $validated['answers'][$question->id];
                $isCorrect = $selectedOption === $question->correct_option;

                if ($isCorrect) {
                    $correctAnswers++;
                }

                $attempt->answers()->create([
                    'quiz_question_id' => $question->id,
                    'selected_option' => $selectedOption,
                    'is_correct' => $isCorrect,
                ]);
            }

            $score = $questions->isNotEmpty()
                ? (int) round(($correctAnswers / $questions->count()) * 100)
                : 0;

            $attempt->update([
                'score' => $score,
                'correct_answers' => $correctAnswers,
            ]);

            return $attempt;
        });

        return redirect()
            ->route('quizzes.show', [$subject, $material, $quiz])
            ->with('success', 'Kuis selesai dikerjakan. Skor Anda: '.$attempt->score.'.');
    }

    public function printAttempt(Request $request, Subject $subject, Material $material, Quiz $quiz, QuizAttempt $attempt): View
    {
        $this->ensureMaterialBelongsToSubject($subject, $material);
        $this->ensureQuizBelongsToMaterial($material, $quiz);
        abort_if($attempt->quiz_id !== $quiz->id, 404);

        $user = $request->user();
        abort_if($user->role === 'siswa' && $attempt->user_id !== $user->id, 403);
        abort_if(! in_array($user->role, ['guru', 'siswa', 'admin'], true), 403);

        $attempt->load(['user', 'answers.question', 'quiz.material.subject']);

        return view('quizzes.print', [
            'attempt' => $attempt,
            'quiz' => $quiz,
            'material' => $material,
            'subject' => $subject,
        ]);
    }

    private function validateQuiz(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question' => ['required', 'string'],
            'questions.*.option_a' => ['required', 'string', 'max:255'],
            'questions.*.option_b' => ['required', 'string', 'max:255'],
            'questions.*.option_c' => ['required', 'string', 'max:255'],
            'questions.*.option_d' => ['required', 'string', 'max:255'],
            'questions.*.correct_option' => ['required', 'in:a,b,c,d'],
            'questions.*.explanation' => ['nullable', 'string'],
            'questions.*.image_url' => ['nullable', 'url'],
            'questions.*.image_file' => ['nullable', 'file', 'image', 'max:4096'],
        ], [
            'questions.required' => 'Tambahkan minimal satu soal untuk kuis ini.',
            'questions.min' => 'Tambahkan minimal satu soal untuk kuis ini.',
        ]);
    }

    private function storeQuestionImage(Request $request, string|int $index): array
    {
        $uploadedFile = $request->file('questions.'.$index.'.image_file');

        if (! $uploadedFile) {
            return [null, null];
        }

        return [
            $uploadedFile->store('quiz-questions', 'public'),
            $uploadedFile->getClientOriginalName(),
        ];
    }

    private function ensureMaterialBelongsToSubject(Subject $subject, Material $material): void
    {
        abort_if($material->subject_id !== $subject->id, 404);
    }

    private function ensureQuizBelongsToMaterial(Material $material, Quiz $quiz): void
    {
        abort_if($quiz->material_id !== $material->id, 404);
    }
}
