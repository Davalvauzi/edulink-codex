<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Subject;
use App\Models\User;
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
            'message' => 'Ringkasan portal ditampilkan di sini agar admin bisa memantau aktivitas pembelajaran dari satu halaman.',
            'role' => 'admin',
            'dashboardStats' => [
                ['label' => 'Total Mapel', 'value' => Subject::query()->count(), 'detail' => 'Mata pelajaran aktif di portal'],
                ['label' => 'Total Materi', 'value' => Material::query()->count(), 'detail' => 'Bab utama yang sudah dibuat guru'],
                ['label' => 'Total Kuis', 'value' => Quiz::query()->count(), 'detail' => 'Latihan soal tersedia'],
                ['label' => 'Attempt Kuis', 'value' => QuizAttempt::query()->count(), 'detail' => 'Pengerjaan kuis oleh siswa'],
            ],
            'progressHighlights' => [
                ['title' => 'Aktivitas Materi', 'description' => 'Pantau jumlah mapel, materi, dan kuis untuk memastikan konten belajar terus bertambah.'],
                ['title' => 'Akses Cepat', 'description' => 'Gunakan menu sidebar untuk berpindah ke dashboard, materi, dan kuis kapan saja.'],
            ],
        ]);
    }

    public function guru(): View
    {
        $subjects = Subject::query()
            ->withCount(['materials', 'materialSubsections'])
            ->latest()
            ->get();

        $recentQuizzes = Quiz::query()
            ->with(['material.subject'])
            ->withCount(['questions', 'attempts'])
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard', [
            'title' => 'Dashboard Guru',
            'message' => '',
            'role' => 'guru',
            'subjects' => $subjects,
            'recentQuizzes' => $recentQuizzes,
            'dashboardStats' => [
                ['label' => 'Total Materi', 'value' => Material::query()->count(), 'detail' => 'Bab utama seluruh mapel'],
                ['label' => 'Total Kuis', 'value' => Quiz::query()->count(), 'detail' => 'Kuis yang sudah dibuat guru'],
                ['label' => 'Attempt Kuis', 'value' => QuizAttempt::query()->count(), 'detail' => 'Pengerjaan kuis oleh siswa'],
            ],
            'progressHighlights' => [
                ['title' => 'Materi Terstruktur', 'description' => 'Gunakan halaman Materi di sidebar untuk melihat semua mapel dan bab dari satu tempat.'],
                ['title' => 'Kuis Terpantau', 'description' => 'Halaman Kuis menampilkan kuis yang sudah dibuat beserta jumlah soal dan attempt siswa.'],
            ],
        ]);
    }

    public function siswa(Request $request): View
    {
        $user = Auth::user();
        $selectedKelas = $this->resolveSelectedKelas($request, $user->kelas);

        $subjects = $this->buildStudentSubjectQuery($selectedKelas)
            ->withCount('materials')
            ->orderBy('name')
            ->get();

        $availableMaterials = Material::query()
            ->whereHas('subject', fn($query) => $query->where('kelas', $selectedKelas))
            ->count();

        $availableQuizzes = Quiz::query()
            ->whereHas('material.subject', fn($query) => $query->where('kelas', $selectedKelas))
            ->count();

        $completedQuizzes = QuizAttempt::query()
            ->where('user_id', $user->id)
            ->whereHas('quiz.material.subject', fn($query) => $query->where('kelas', $selectedKelas))
            ->count();

        return view('dashboard', [
            'title' => 'Dashboard Siswa',
            'message' => 'Dashboard siswa menampilkan progress belajar, daftar mapel, dan perkembangan latihan soal dalam satu halaman.',
            'role' => $user->role,
            'user' => $user,
            'subjects' => $subjects,
            'selectedKelas' => $selectedKelas,
            'dashboardStats' => [
                ['label' => 'Mapel Aktif', 'value' => $subjects->count(), 'detail' => 'Mapel ' . User::kelasLabel($selectedKelas)],
                ['label' => 'Materi Aktif', 'value' => $availableMaterials, 'detail' => 'Bab utama tersedia'],
                ['label' => 'Kuis Tersedia', 'value' => $availableQuizzes, 'detail' => 'Bisa dibuka dari menu Kuis'],
                ['label' => 'Kuis Selesai', 'value' => $completedQuizzes, 'detail' => 'Attempt yang sudah dikirim'],
            ],
            'progressHighlights' => [
                ['title' => 'Materi Belajar', 'description' => 'Buka menu Materi untuk langsung menuju Bahasa Inggris.'],
                ['title' => 'Latihan Soal', 'description' => 'Kuis yang tersedia bisa dibuka dari menu sidebar tanpa harus kembali ke materi.'],
            ],
        ]);
    }

    public function adminMaterials(): RedirectResponse
    {
        return redirect()->route('admin.dashboard');
    }

    public function guruMaterials(): RedirectResponse
    {
        return $this->redirectToEnglishSubject('guru');
    }

    public function siswaMaterials(Request $request): RedirectResponse
    {
        return $this->redirectToEnglishSubject('siswa', $request->user()->kelas);
    }

    public function adminQuizzes(): View
    {
        return view('quizzes.index', [
            'title' => 'Halaman Kuis',
            'role' => 'admin',
            'quizzes' => Quiz::query()->with(['material.subject'])->withCount(['questions', 'attempts'])->latest()->get(),
            'emptyActionRoute' => route('admin.materials'),
            'emptyActionLabel' => 'Buka Halaman Materi',
        ]);
    }

    public function guruQuizzes(): View
    {
        return view('quizzes.index', [
            'title' => 'Halaman Kuis',
            'role' => 'guru',
            'quizzes' => Quiz::query()->with(['material.subject'])->withCount(['questions', 'attempts'])->latest()->get(),
            'emptyActionRoute' => route('guru.materials'),
            'emptyActionLabel' => 'Kembali ke Materi',
        ]);
    }

    public function siswaQuizzes(Request $request): View
    {
        $user = $request->user();

        $quizzes = Quiz::query()
            ->with(['material.subject'])
            ->withCount('questions')
            ->whereHas('material.subject', fn($query) => $query->where('kelas', $user->kelas))
            ->latest()
            ->get()
            ->map(function (Quiz $quiz) use ($user) {
                $quiz->latest_attempt = QuizAttempt::query()
                    ->where('quiz_id', $quiz->id)
                    ->where('user_id', $user->id)
                    ->latest('submitted_at')
                    ->latest('id')
                    ->first();

                return $quiz;
            });

        return view('quizzes.index', [
            'title' => 'Halaman Kuis',
            'role' => 'siswa',
            'user' => $user,
            'quizzes' => $quizzes,
            'emptyActionRoute' => route('siswa.materials'),
            'emptyActionLabel' => 'Kembali ke Materi',
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
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'kelas' => ['required', 'in:' . implode(',', array_keys(User::kelasOptions()))],
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

    private function resolveSelectedKelas(Request $request, string $defaultKelas): string
    {
        $selectedKelas = $request->query('kelas', $defaultKelas);

        return User::isValidKelas($selectedKelas) ? $selectedKelas : $defaultKelas;
    }

    private function buildStudentSubjectQuery(string $selectedKelas)
    {
        return Subject::query()->where('kelas', $selectedKelas);
    }

    private function redirectToEnglishSubject(string $role, ?string $kelas = null): RedirectResponse
    {
        $subject = Subject::query()
            ->when($kelas, fn($query) => $query->where('kelas', $kelas))
            ->where('name', 'Bahasa Inggris')
            ->first();

        if (! $subject) {
            $subject = Subject::query()
                ->when($kelas, fn($query) => $query->where('kelas', $kelas))
                ->orderBy('name')
                ->first();
        }

        if (! $subject) {
            return redirect()
                ->route($role . '.dashboard')
                ->with('error', 'Mata pelajaran Bahasa Inggris belum tersedia.');
        }

        return redirect()->route('subjects.show', $subject);
    }
}
