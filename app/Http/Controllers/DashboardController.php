<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('dashboard', [
            'title' => 'Dashboard Admin',
            'message' => 'Kelola data sekolah, pengguna, dan pengaturan sistem dari satu tempat.',
            'role' => Auth::user()->role,
        ]);
    }

    public function guru(): View
    {
        $subjects = Subject::query()
            ->withCount('materials')
            ->latest()
            ->get();

        return view('dashboard', [
            'title' => 'Dashboard Guru',
            'message' => 'Tambahkan mata pelajaran baru untuk tiap kelas dan kelola materi di dalam setiap mata pelajaran.',
            'role' => Auth::user()->role,
            'subjects' => $subjects,
        ]);
    }

    public function siswa(Request $request): View
    {
        $user = Auth::user();
        $selectedKelas = $request->query('kelas', $user->kelas);
        $selectedKelas = in_array($selectedKelas, ['10', '11', '12'], true) ? $selectedKelas : $user->kelas;

        $subjects = Subject::query()
            ->withCount('materials')
            ->where('kelas', $selectedKelas)
            ->orderBy('name')
            ->get();

        return view('dashboard', [
            'title' => 'Dashboard Siswa',
            'message' => 'Lihat ringkasan kegiatan belajar, buka materi tiap mata pelajaran, dan kelola profil dari halaman khusus.',
            'role' => $user->role,
            'user' => $user,
            'subjects' => $subjects,
            'selectedKelas' => $selectedKelas,
        ]);
    }

    public function showSubject(Request $request, Subject $subject): View
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

    public function showSiswaProfile(Request $request): View
    {
        $user = $request->user();

        abort_if($user->role !== 'siswa', 403);

        return view('siswa.profile', [
            'title' => 'Profil Siswa',
            'role' => $user->role,
            'user' => $user,
        ]);
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        abort_if($request->user()->role !== 'guru', 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kelas' => ['required', 'in:10,11,12'],
        ]);

        Subject::query()->create([
            'name' => $data['name'],
            'kelas' => $data['kelas'],
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('guru.dashboard')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function storeMaterial(Request $request, Subject $subject): RedirectResponse
    {
        abort_if($request->user()->role !== 'guru', 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $filePath = null;
        $fileName = null;

        if ($request->hasFile('file')) {
            $storedFile = $request->file('file');
            $filePath = $storedFile->store('materials', 'public');
            $fileName = $storedFile->getClientOriginalName();
        }

        Material::query()->create([
            'subject_id' => $subject->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'file_path' => $filePath,
            'file_name' => $fileName,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('subjects.show', $subject)
            ->with('success', 'Materi berhasil ditambahkan ke mata pelajaran.');
    }

    public function updateSiswaProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user->role !== 'siswa', 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'kelas' => ['required', 'in:10,11,12'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->kelas = $data['kelas'];

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return redirect()
            ->route('siswa.profile')
            ->with('success', 'Profil siswa berhasil diperbarui.');
    }
}
