<?php

namespace App\Http\Controllers;

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

        $subject->load(['creator', 'materials.creator']);

        return view('subjects.show', [
            'title' => 'Materi '.$subject->name,
            'role' => $user->role,
            'user' => $user,
            'subject' => $subject,
        ]);
    }
}
