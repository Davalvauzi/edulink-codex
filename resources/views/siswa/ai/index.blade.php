@extends('layouts.portal')

@section('sidebar')
    <a class="{{ request()->routeIs('siswa.ai.index') ? 'active' : '' }}" href="{{ route('siswa.ai.index') }}">
        Tanya AI
        <span>Konsultasi materi dan bahas kesalahan kuis</span>
    </a>
    @if ($subject)
        <div class="static-item">
            Mata Pelajaran
            <span>{{ $subject->name }}</span>
        </div>
    @endif
    @if ($material)
        <div class="static-item">
            Materi
            <span>{{ $material->title }}</span>
        </div>
    @endif
    @if ($subsection)
        <div class="static-item">
            Sub Bab
            <span>{{ $subsection->title }}</span>
        </div>
    @endif
    @if ($quiz)
        <div class="static-item">
            Kuis
            <span>{{ $quiz->title }}</span>
        </div>
    @endif
@endsection

@section('heading', 'Tanya AI')
@section('subtitle', $contextDescription)

@section('actions')
    @if ($quiz)
        <a class="btn btn-soft" href="{{ route('quizzes.show', [$subject, $material, $quiz]) }}">Kembali ke Kuis</a>
    @elseif ($material)
        <a class="btn btn-soft" href="{{ route('materials.show', [$subject, $material]) }}">Kembali ke Materi</a>
    @else
        <a class="btn btn-soft" href="{{ route('siswa.dashboard') }}">Kembali</a>
    @endif
@endsection

@section('content')
    <section class="cards">
        <article class="card">
            <strong>Konteks Belajar</strong>
            <p>{{ $contextTitle }}</p>
        </article>
        <article class="card">
            <strong>Riwayat Pesan</strong>
            <p>{{ $conversation->messages->count() }} pesan tersimpan pada konteks ini.</p>
        </article>
        <article class="card">
            <strong>Jawaban Salah Terdeteksi</strong>
            <p>{{ $wrongAnswers->count() }} butir terbaru siap dipakai AI untuk membantu Anda.</p>
        </article>
    </section>

    @if ($wrongAnswers->isNotEmpty())
        <section class="meta stack">
            <div>
                <strong>Kesalahan yang Bisa Dibahas</strong>
                <p>AI akan memakai daftar ini sebagai konteks tambahan agar penjelasannya lebih relevan dengan kebutuhan Anda.</p>
            </div>

            <div class="question-list">
                @foreach ($wrongAnswers as $wrongAnswer)
                    <article class="question-card compact">
                        <div class="question-card-header">
                            <div>
                                <strong>{{ $wrongAnswer['quiz'] }}</strong>
                                <p>{{ $wrongAnswer['material'] }}</p>
                            </div>
                            <span class="answer-pill wrong">Perlu Dibahas</span>
                        </div>
                        <p class="question-text">{{ $wrongAnswer['question'] }}</p>
                        <div class="explanation-card">
                            <strong>Jawaban Anda: {{ $wrongAnswer['selected_option'] }}</strong>
                            <p>Jawaban benar: {{ $wrongAnswer['correct_option'] }}</p>
                        </div>
                        @if ($wrongAnswer['explanation'])
                            <div class="explanation-card">
                                <strong>Pembahasan Guru</strong>
                                <p>{{ $wrongAnswer['explanation'] }}</p>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="meta stack">
        <div class="section-title">
            <div>
                <strong>Percakapan</strong>
                <p>Tanyakan konsep yang belum paham, minta rangkuman sub bab, atau minta dibantu membahas kesalahan saat kuis.</p>
            </div>
        </div>

        @if ($conversation->messages->isEmpty())
            <div class="empty-state">
                Belum ada percakapan. Coba mulai dengan pertanyaan seperti “jelaskan lagi inti materi ini”, “kenapa jawaban saya salah”, atau “beri contoh soal serupa”.
            </div>
        @else
            <div class="chat-thread">
                @foreach ($conversation->messages as $message)
                    <article class="chat-message {{ $message->role === 'assistant' ? 'assistant' : 'user' }}">
                        <span class="chat-role">{{ $message->role === 'assistant' ? 'AI Tutor' : 'Anda' }}</span>
                        <div class="prose chat-copy">
                            {!! nl2br(e($message->content)) !!}
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <form class="stack" method="POST" action="{{ route('siswa.ai.store') }}">
            @csrf
            @if ($subject)
                <input type="hidden" name="subject" value="{{ $subject->id }}">
            @endif
            @if ($material)
                <input type="hidden" name="material" value="{{ $material->id }}">
            @endif
            @if ($subsection)
                <input type="hidden" name="subsection" value="{{ $subsection->id }}">
            @endif
            @if ($quiz)
                <input type="hidden" name="quiz" value="{{ $quiz->id }}">
            @endif
            @if ($quizAttempt)
                <input type="hidden" name="attempt" value="{{ $quizAttempt->id }}">
            @endif

            <div class="field field-full">
                <label for="message">Pertanyaan Anda</label>
                <textarea id="message" name="message" placeholder="Contoh: kenapa jawaban saya di soal nomor 3 salah? Jelaskan pelan-pelan dan beri contoh baru.">{{ old('message') }}</textarea>
            </div>

            <div class="subsection-actions">
                <button class="btn btn-primary" type="submit">Kirim ke AI</button>
            </div>
        </form>
    </section>
@endsection
