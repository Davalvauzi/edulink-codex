@extends('layouts.portal')

@section('sidebar')
    <div class="static-item">
        Halaman Aktif
        <span>Kuis</span>
    </div>
@endsection

@section('heading', 'Kuis')
@section('subtitle', 'Semua kuis yang dibuat guru dari halaman materi akan muncul di sini. Jika belum ada kuis, halaman ini akan tetap bersih dan menyediakan tombol kembali ke materi.')

@section('content')
    <section class="meta">
        <div class="section-title">
            <div>
                <strong>Daftar Kuis</strong>
                <p>Buka kuis untuk melihat soal, hasil pengerjaan, atau review attempt terbaru.</p>
            </div>
        </div>

        @if ($quizzes->isEmpty())
            <div class="empty-state">
                Belum ada kuis yang tersedia saat ini.
                <div style="margin-top: 14px;">
                    <a class="btn btn-soft" href="{{ $emptyActionRoute }}">{{ $emptyActionLabel }}</a>
                </div>
            </div>
        @else
            <div class="quiz-grid">
                @foreach ($quizzes as $quiz)
                    <a class="subject-item" href="{{ route('quizzes.show', [$quiz->material->subject, $quiz->material, $quiz]) }}">
                        <span class="subject-badge">{{ $quiz->material->subject->name }}</span>
                        <h3>{{ $quiz->title }}</h3>
                        <p>{{ $quiz->material->title }}</p>
                        <p class="material-summary">{{ $quiz->questions_count }} soal tersedia.</p>
                        @if ($role === 'guru')
                            <p class="material-summary">{{ $quiz->attempts_count }} attempt siswa tercatat.</p>
                        @elseif ($role === 'siswa')
                            <p class="material-summary">
                                {{ $quiz->latest_attempt ? 'Skor terakhir: '.$quiz->latest_attempt->score : 'Belum pernah dikerjakan' }}
                            </p>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
