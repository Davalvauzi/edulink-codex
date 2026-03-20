@extends('layouts.portal')

@php
    $wrongAnswers = $latestAttempt?->answers->where('is_correct', false) ?? collect();
@endphp

@section('sidebar')
    <a href="{{ route('materials.show', [$subject, $material]) }}">
        Kembali ke Detail Materi
        <span>{{ $material->title }}</span>
    </a>
    <div class="static-item">
        Kuis Aktif
        <span>{{ $quiz->title }}</span>
    </div>
    <div class="static-item">
        Jumlah Soal
        <span>{{ $quiz->questions->count() }} soal pilihan ganda</span>
    </div>
@endsection

@section('heading', $quiz->title)
@section('subtitle', $role === 'guru' ? 'Guru dapat meninjau susunan soal yang terhubung ke materi ini.' : 'Kerjakan semua soal pilihan ganda. Setelah dikirim, skor dan pembahasan jawaban yang salah akan tampil di halaman ini.')

@section('actions')
    @if ($role === 'guru')
        <a class="btn btn-soft" href="{{ route('guru.materials.quizzes.create', [$subject, $material]) }}">Buat Kuis Baru</a>
    @endif
    <a class="btn btn-soft" href="{{ route('materials.show', [$subject, $material]) }}">Kembali</a>
@endsection

@section('content')
    <section class="cards">
        <article class="card">
            <strong>Mata Pelajaran</strong>
            <p>{{ $subject->name }}</p>
        </article>
        <article class="card">
            <strong>Materi</strong>
            <p>{{ $material->title }}</p>
        </article>
        <article class="card">
            <strong>Dibuat Oleh</strong>
            <p>{{ $quiz->creator?->name ?? 'Guru tidak diketahui' }}</p>
        </article>
    </section>

    <section class="meta stack">
        @if ($quiz->description)
            <div>
                <strong>Instruksi</strong>
                <p>{{ $quiz->description }}</p>
            </div>
        @endif

        @if ($role === 'guru')
            <div class="question-list">
                @foreach ($quiz->questions as $question)
                    <article class="question-card compact">
                        <div class="question-card-header">
                            <div>
                                <strong>Soal {{ $question->position }}</strong>
                                <p>Preview soal pilihan ganda untuk guru.</p>
                            </div>
                            <span class="answer-pill">Kunci {{ strtoupper($question->correct_option) }}</span>
                        </div>
                        @if ($question->image_source)
                            <div class="question-media">
                                <img src="{{ $question->image_source }}" alt="Gambar soal {{ $question->position }}">
                                <div class="question-media-caption">{{ $question->image_name ?: 'Gambar dari tautan eksternal' }}</div>
                            </div>
                        @endif
                        <p class="question-text">{{ $question->question }}</p>
                        <div class="choice-list">
                            @foreach ($question->options as $key => $option)
                                <div class="choice-item {{ $question->correct_option === $key ? 'correct' : '' }}">
                                    <strong>{{ strtoupper($key) }}.</strong>
                                    <span>{{ $option }}</span>
                                </div>
                            @endforeach
                        </div>
                        @if ($question->explanation)
                            <div class="explanation-card">
                                <strong>Pembahasan</strong>
                                <p>{{ $question->explanation }}</p>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            @if ($latestAttempt)
                <div class="result-panel">
                    <div>
                        <strong>Skor Terakhir</strong>
                        <p>Anda menjawab benar {{ $latestAttempt->correct_answers }} dari {{ $latestAttempt->total_questions }} soal.</p>
                    </div>
                    <div class="result-score">{{ $latestAttempt->score }}</div>
                </div>
            @endif

            <form class="stack" method="POST" action="{{ route('quizzes.submit', [$subject, $material, $quiz]) }}">
                @csrf

                <div class="question-list">
                    @foreach ($quiz->questions as $question)
                        <article class="question-card compact">
                            <div class="question-card-header">
                                <div>
                                    <strong>Soal {{ $question->position }}</strong>
                                    <p>Pilih satu jawaban yang paling tepat.</p>
                                </div>
                            </div>
                            @if ($question->image_source)
                                <div class="question-media">
                                    <img src="{{ $question->image_source }}" alt="Gambar soal {{ $question->position }}">
                                    <div class="question-media-caption">{{ $question->image_name ?: 'Gambar dari tautan eksternal' }}</div>
                                </div>
                            @endif
                            <p class="question-text">{{ $question->question }}</p>
                            <div class="radio-list">
                                @foreach ($question->options as $key => $option)
                                    <label class="radio-option">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $key }}" @checked(old('answers.'.$question->id) === $key)>
                                        <span><strong>{{ strtoupper($key) }}.</strong> {{ $option }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>

                <button class="btn btn-primary" type="submit">Kirim Jawaban</button>
            </form>

            @if ($latestAttempt)
                <section class="meta stack">
                    <div>
                        <strong>Review Jawaban Salah</strong>
                        <p>Pembahasan hanya ditampilkan untuk soal yang belum tepat agar siswa bisa langsung belajar dari hasil kuis.</p>
                    </div>

                    @if ($wrongAnswers->isEmpty())
                        <div class="empty-state">Semua jawaban pada percobaan terakhir sudah benar. Bagus sekali.</div>
                    @else
                        <div class="question-list">
                            @foreach ($wrongAnswers as $answer)
                                <article class="question-card compact">
                                    <div class="question-card-header">
                                        <div>
                                            <strong>Soal {{ $answer->question->position }}</strong>
                                            <p>Fokus pada pembahasan agar konsep yang keliru langsung diperbaiki.</p>
                                        </div>
                                        <span class="answer-pill wrong">Jawaban Anda {{ strtoupper($answer->selected_option) }}</span>
                                    </div>
                                    @if ($answer->question->image_source)
                                        <div class="question-media">
                                            <img src="{{ $answer->question->image_source }}" alt="Gambar soal {{ $answer->question->position }}">
                                            <div class="question-media-caption">{{ $answer->question->image_name ?: 'Gambar dari tautan eksternal' }}</div>
                                        </div>
                                    @endif
                                    <p class="question-text">{{ $answer->question->question }}</p>
                                    <div class="explanation-card">
                                        <strong>Jawaban Benar: {{ strtoupper($answer->question->correct_option) }}</strong>
                                        <p>{{ $answer->question->explanation ?: 'Belum ada pembahasan tambahan dari guru untuk soal ini.' }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif
        @endif
    </section>
@endsection
