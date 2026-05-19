<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\QuizAttempt;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function create(Request $request): View
    {
        abort_if($request->user()->role !== 'guru', 403);

        return view('subjects.create', [
            'title' => 'Tambah Mata Pelajaran',
            'role' => $request->user()->role,
            'user' => $request->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()->role !== 'guru', 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kelas' => ['required', 'in:'.implode(',', array_keys(User::kelasOptions()))],
        ]);

        $subject = Subject::query()->create([
            'name' => $data['name'],
            'kelas' => $data['kelas'],
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('subjects.show', $subject)
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function show(Request $request, Subject $subject): View
    {
        $user = $request->user();

        abort_if(! in_array($user->role, ['guru', 'siswa'], true), 403);

        $subject->load([
            'creator',
            'materials' => fn ($query) => $query
                ->with('creator')
                ->withCount('quizzes')
                ->latest('created_at')
                ->latest('id'),
        ]);

        if ($user->role === 'siswa') {
            $subject->materials->each(function (Material $material) use ($user) {
                $completedQuizzes = QuizAttempt::query()
                    ->where('user_id', $user->id)
                    ->whereHas('quiz', fn ($query) => $query->where('material_id', $material->id))
                    ->distinct('quiz_id')
                    ->count('quiz_id');

                $material->completed_quizzes_count = $completedQuizzes;
                $material->learning_progress_percentage = $material->quizzes_count > 0
                    ? (int) round(($completedQuizzes / $material->quizzes_count) * 100)
                    : 0;
            });
        }

        return view('subjects.show', [
            'title' => 'Materi '.$subject->name,
            'role' => $user->role,
            'user' => $user,
            'subject' => $subject,
        ]);
    }
}
