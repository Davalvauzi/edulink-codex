<?php

namespace App\Http\Controllers;

use App\Models\Subject;
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
            'kelas' => ['required', 'in:10,11,12'],
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
                ->with('subsections', function ($subsectionQuery) use ($user) {
                    $subsectionQuery->with(
                        $user->role === 'siswa'
                            ? ['progressRecords' => fn ($progressQuery) => $progressQuery->where('user_id', $user->id)]
                            : ['progressRecords']
                    );
                }),
        ]);

        $materials = $subject->materials->map(function ($material) use ($user) {
            $material->subsections_count = $material->subsections->count();
            $material->completed_subsections_count = $user->role === 'siswa'
                ? $material->subsections->filter(fn ($subsection) => $subsection->progressRecords->isNotEmpty())->count()
                : 0;
            $material->progress_percentage = $material->subsections_count > 0
                ? (int) round(($material->completed_subsections_count / $material->subsections_count) * 100)
                : 0;

            return $material;
        });

        return view('subjects.show', [
            'title' => 'Materi '.$subject->name,
            'role' => $user->role,
            'user' => $user,
            'subject' => $subject,
            'materials' => $materials,
        ]);
    }
}
