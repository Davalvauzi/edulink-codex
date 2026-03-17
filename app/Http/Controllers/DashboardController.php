<?php

namespace App\Http\Controllers;

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
        return view('dashboard', [
            'title' => 'Dashboard Guru',
            'message' => 'Pantau kelas, materi, dan aktivitas belajar siswa dengan cepat.',
            'role' => Auth::user()->role,
        ]);
    }

    public function siswa(): View
    {
        return view('dashboard', [
            'title' => 'Dashboard Siswa',
            'message' => 'Lihat ringkasan kegiatan belajar, jadwal penting, dan lengkapi profil Anda dari panel ini.',
            'role' => Auth::user()->role,
            'user' => Auth::user(),
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
            ->route('siswa.dashboard')
            ->with('success', 'Profil siswa berhasil diperbarui.');
    }
}
