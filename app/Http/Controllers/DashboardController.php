<?php

namespace App\Http\Controllers;

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
            'message' => 'Kelola mata pelajaran dan materi lewat halaman khusus agar alur kerja guru lebih rapi.',
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
            'message' => 'Lihat daftar mata pelajaran, buka tiap materi pada halaman tersendiri, dan kelola profil dari halaman khusus.',
            'role' => $user->role,
            'user' => $user,
            'subjects' => $subjects,
            'selectedKelas' => $selectedKelas,
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
