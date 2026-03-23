<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\Material;
use App\Models\MaterialSubsection;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Subject;
use App\Services\StudentAiTutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentAiController extends Controller
{
    public function __construct(private readonly StudentAiTutor $tutor)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_if($user->role !== 'siswa', 403);

        [$subject, $material, $subsection, $quiz, $quizAttempt] = $this->resolveContext($request);

        $conversation = AiConversation::query()
            ->with(['messages', 'quizAttempt.quiz', 'quiz'])
            ->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'context_hash' => $this->buildContextHash($subject, $material, $subsection, $quiz, $quizAttempt),
                ],
                [
                    'subject_id' => $subject?->id,
                    'material_id' => $material?->id,
                    'material_subsection_id' => $subsection?->id,
                    'quiz_id' => $quiz?->id,
                    'quiz_attempt_id' => $quizAttempt?->id,
                    'title' => $this->tutor->buildConversationTitle($material, $subsection, $quiz, $quizAttempt),
                ]
            );

        $pageContext = $this->tutor->buildPageContext($user, $material, $subsection, $quiz, $quizAttempt);

        return view('siswa.ai.index', [
            'title' => 'Tanya AI',
            'role' => $user->role,
            'user' => $user,
            'subject' => $subject,
            'material' => $material,
            'subsection' => $subsection,
            'quiz' => $quiz,
            'quizAttempt' => $quizAttempt,
            'conversation' => $conversation,
            'contextTitle' => $pageContext['contextTitle'],
            'contextDescription' => $pageContext['contextDescription'],
            'wrongAnswers' => $pageContext['wrongAnswers'],
            'recentAttempts' => $pageContext['recentAttempts'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user->role !== 'siswa', 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'subject' => ['nullable', 'integer'],
            'material' => ['nullable', 'integer'],
            'subsection' => ['nullable', 'integer'],
            'quiz' => ['nullable', 'integer'],
            'attempt' => ['nullable', 'integer'],
        ]);

        [$subject, $material, $subsection, $quiz, $quizAttempt] = $this->resolveContext($request);

        $conversation = AiConversation::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'context_hash' => $this->buildContextHash($subject, $material, $subsection, $quiz, $quizAttempt),
            ],
            [
                'subject_id' => $subject?->id,
                'material_id' => $material?->id,
                'material_subsection_id' => $subsection?->id,
                'quiz_id' => $quiz?->id,
                'quiz_attempt_id' => $quizAttempt?->id,
                'title' => $this->tutor->buildConversationTitle($material, $subsection, $quiz, $quizAttempt),
            ]
        );

        $conversation->messages()->create([
            'role' => 'user',
            'content' => trim($validated['message']),
        ]);

        try {
            $conversation->load('messages');
            $assistantReply = $this->tutor->reply($conversation);

            DB::transaction(function () use ($conversation, $assistantReply) {
                $conversation->messages()->create([
                    'role' => 'assistant',
                    'content' => $assistantReply['content'],
                    'response_id' => $assistantReply['response_id'],
                    'prompt_tokens' => $assistantReply['prompt_tokens'],
                    'completion_tokens' => $assistantReply['completion_tokens'],
                    'total_tokens' => $assistantReply['total_tokens'],
                ]);
            });
        } catch (\Throwable $exception) {
            return redirect()
                ->route('siswa.ai.index', $this->buildRouteParameters($subject, $material, $subsection, $quiz, $quizAttempt))
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('siswa.ai.index', $this->buildRouteParameters($subject, $material, $subsection, $quiz, $quizAttempt))
            ->with('success', 'Jawaban dari AI sudah siap dibaca.');
    }

    private function resolveContext(Request $request): array
    {
        $user = $request->user();

        $subject = null;
        $material = null;
        $subsection = null;
        $quiz = null;
        $quizAttempt = null;

        if ($request->filled('subject')) {
            $subject = Subject::query()
                ->whereKey($request->integer('subject'))
                ->where('kelas', $user->kelas)
                ->firstOrFail();
        }

        if ($request->filled('material')) {
            $material = Material::query()
                ->with(['subject', 'subsections'])
                ->whereKey($request->integer('material'))
                ->whereHas('subject', fn ($query) => $query->where('kelas', $user->kelas))
                ->firstOrFail();

            $subject ??= $material->subject;
        }

        if ($request->filled('subsection')) {
            $subsection = MaterialSubsection::query()
                ->with(['material.subject'])
                ->whereKey($request->integer('subsection'))
                ->whereHas('material.subject', fn ($query) => $query->where('kelas', $user->kelas))
                ->firstOrFail();

            $material ??= $subsection->material;
            $subject ??= $subsection->material->subject;
        }

        if ($request->filled('quiz')) {
            $quiz = Quiz::query()
                ->with(['material.subject', 'questions'])
                ->whereKey($request->integer('quiz'))
                ->whereHas('material.subject', fn ($query) => $query->where('kelas', $user->kelas))
                ->firstOrFail();

            $material ??= $quiz->material;
            $subject ??= $quiz->material->subject;
        }

        if ($request->filled('attempt')) {
            $quizAttempt = QuizAttempt::query()
                ->with(['quiz.material.subject', 'answers.question'])
                ->whereKey($request->integer('attempt'))
                ->where('user_id', $user->id)
                ->firstOrFail();

            $quiz ??= $quizAttempt->quiz;
            $material ??= $quizAttempt->quiz?->material;
            $subject ??= $quizAttempt->quiz?->material?->subject;
        }

        if ($subject && $material && $material->subject_id !== $subject->id) {
            abort(404);
        }

        if ($material && $subsection && $subsection->material_id !== $material->id) {
            abort(404);
        }

        if ($material && $quiz && $quiz->material_id !== $material->id) {
            abort(404);
        }

        if ($quiz && $quizAttempt && $quizAttempt->quiz_id !== $quiz->id) {
            abort(404);
        }

        return [$subject, $material, $subsection, $quiz, $quizAttempt];
    }

    private function buildContextHash(?Subject $subject, ?Material $material, ?MaterialSubsection $subsection, ?Quiz $quiz, ?QuizAttempt $quizAttempt): string
    {
        return implode(':', [
            'subject='.($subject?->id ?? 'none'),
            'material='.($material?->id ?? 'none'),
            'subsection='.($subsection?->id ?? 'none'),
            'quiz='.($quiz?->id ?? 'none'),
            'attempt='.($quizAttempt?->id ?? 'none'),
        ]);
    }

    private function buildRouteParameters(?Subject $subject, ?Material $material, ?MaterialSubsection $subsection, ?Quiz $quiz, ?QuizAttempt $quizAttempt): array
    {
        return array_filter([
            'subject' => $subject?->id,
            'material' => $material?->id,
            'subsection' => $subsection?->id,
            'quiz' => $quiz?->id,
            'attempt' => $quizAttempt?->id,
        ]);
    }
}
