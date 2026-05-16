@extends('layouts.portal')

@section('sidebar')
    <a href="{{ route('subjects.show', $subject) }}">
        Kembali ke Mata Pelajaran
        <span>{{ $subject->name }}</span>
    </a>
    <div class="static-item">
        Materi Aktif
        <span>{{ $material->title }}</span>
    </div>
    <div class="static-item">
        Mata Pelajaran
        <span>{{ $subject->name }}</span>
    </div>
@endsection

@section('heading', $material->title)
@section('subtitle', 'Konten, file, dan kuis pada bab ini ditampilkan dalam satu halaman.')

@section('actions')
    @if ($role === 'siswa')
        <a class="btn btn-primary" href="{{ route('siswa.ai.index', ['subject' => $subject->id, 'material' => $material->id]) }}">Tanya AI</a>
    @endif
    @if ($role === 'guru')
        <a class="btn btn-soft" href="{{ route('guru.materials.edit', [$subject, $material]) }}">Edit</a>
        <form method="POST" action="{{ route('guru.materials.destroy', [$subject, $material]) }}" onsubmit="return confirm('Hapus materi ini?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger" type="submit">Hapus</button>
        </form>
    @endif
    <a class="btn btn-soft" href="{{ route('subjects.show', $subject) }}">Kembali</a>
@endsection

@section('content')
    <section class="meta compact">
        <div class="section-title">
            <div>
                <strong>Gambar Materi</strong>
            </div>
        </div>

        @if ($material->image_source)
            <figure class="material-hero-image">
                <img src="{{ $material->image_source }}" alt="Gambar materi {{ $material->title }}">
                <figcaption>{{ $material->image_name ?? 'Gambar materi' }}</figcaption>
            </figure>
        @else
            <div class="empty-state">Materi ini belum memiliki gambar.</div>
        @endif
    </section>

    <section class="meta stack material-body">
        <div class="material-description">
            <strong>Bab Utama</strong>
            <div class="prose">{!! $material->description !!}</div>
        </div>

        <div>
            <strong>File Pendukung</strong>
            @if ($material->file_path)
                <p>
                    <a class="link-inline" href="{{ asset('storage/'.$material->file_path) }}" target="_blank" rel="noopener">
                        {{ $material->file_name ?? 'Lihat PDF' }}
                    </a>
                </p>
            @else
                <p>Materi ini belum memiliki file PDF.</p>
            @endif
        </div>
    </section>

    <section class="meta">
        <div class="section-title">
            <div>
                <strong>Kuis dan Latihan Soal</strong>
                <p>{{ $role === 'guru' ? 'Buat kuis pilihan ganda untuk materi ini.' : 'Setelah membaca materi, buka kuis untuk latihan lalu lihat skor dan pembahasan jawaban yang salah.' }}</p>
            </div>
            @if ($role === 'guru')
                <a class="btn btn-primary btn-section" href="{{ route('guru.materials.quizzes.create', [$subject, $material]) }}">Buat Kuis</a>
            @endif
        </div>

        @if ($quizzes->isEmpty())
            <div class="empty-state">Belum ada kuis pada materi ini.</div>
        @else
            <div class="quiz-grid">
                @foreach ($quizzes as $quiz)
                    <article class="subsection-item card-mode">
                        <div class="subsection-content">
                            <span class="subject-badge">Kuis {{ $loop->iteration }}</span>
                            <h3>{{ $quiz->title }}</h3>
                            <p>{{ $quiz->description ?: 'Kuis pilihan ganda untuk mengukur pemahaman siswa pada materi ini.' }}</p>
                            <p class="material-summary">{{ $quiz->questions_count }} soal tersedia. Dibuat oleh {{ $quiz->creator?->name ?? 'guru' }}.</p>
                        </div>

                        <div class="subsection-actions">
                            <a class="btn btn-soft" href="{{ route('quizzes.show', [$subject, $material, $quiz]) }}">
                                {{ $role === 'guru' ? 'Lihat Kuis' : 'Kerjakan Kuis' }}
                            </a>
                            @if ($role === 'guru')
                                <form method="POST" action="{{ route('guru.materials.quizzes.destroy', [$subject, $material, $quiz]) }}" onsubmit="return confirm('Hapus kuis ini beserta attempt siswa dan riwayat chat AI terkait?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" type="submit">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

@endsection
