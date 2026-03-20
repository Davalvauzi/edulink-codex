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
        Kelas
        <span>{{ $subject->kelas }}</span>
    </div>
@endsection

@section('heading', $material->title)
@section('subtitle', 'Setiap materi sekarang memiliki halaman detail sendiri agar konten, file, dan pengelolaan materi lebih terstruktur.')

@section('actions')
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
                <strong>Ringkasan Bab</strong>
                <p>Informasi utama bab ditampilkan ringkas agar fokus tetap ke isi materi dan latihan.</p>
            </div>
        </div>

        <div class="info-strip">
            <article class="mini-info">
                <span>Mata Pelajaran</span>
                <strong>{{ $subject->name }}</strong>
                <p>Kelas {{ $subject->kelas }}</p>
            </article>
            <article class="mini-info">
                <span>Dibuat Oleh</span>
                <strong>{{ $material->creator?->name ?? 'Guru tidak diketahui' }}</strong>
                <p>Materi utama</p>
            </article>
            <article class="mini-info">
                <span>Terakhir Diperbarui</span>
                <strong>{{ $material->updated_at?->format('d M Y') }}</strong>
                <p>{{ $material->updated_at?->format('H:i') }}</p>
            </article>
            <article class="mini-info">
                <span>Total Sub Bab</span>
                <strong>{{ $totalSubsections }}</strong>
                <p>Sub bab tersedia</p>
            </article>
        </div>
    </section>

    @if ($role === 'siswa' && $totalSubsections > 0)
        <section class="meta">
            <div class="progress-panel">
                <div>
                    <strong>Progress Belajar Bab</strong>
                    <p>Setelah membaca sub bab, progress bab utama akan bertambah otomatis. Saat ini Anda sudah menyelesaikan {{ $completedSubsections }} dari {{ $totalSubsections }} sub bab.</p>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" style="width: {{ $progressPercentage }}%;"></div>
                </div>
                <span class="progress-value">{{ $progressPercentage }}%</span>
            </div>
        </section>
    @endif

    <section class="meta stack">
        <div>
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
                <p>{{ $role === 'guru' ? 'Buat kuis pilihan ganda untuk materi ini setelah bab tersedia.' : 'Setelah membaca materi, buka kuis untuk latihan lalu lihat skor dan pembahasan jawaban yang salah.' }}</p>
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
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="meta">
        <div class="section-title">
            <div>
                <strong>Daftar Sub Bab</strong>
                <p>{{ $role === 'guru' ? 'Kelola pembahasan bertahap di bawah bab utama ini.' : 'Buka sub bab untuk membaca isi materi sekaligus menambah progress belajar.' }}</p>
            </div>
            @if ($role === 'guru')
                <a class="btn btn-primary btn-section" href="{{ route('guru.materials.subsections.create', [$subject, $material]) }}">Tambah Sub Bab</a>
            @endif
        </div>

        @if ($subsections->isEmpty())
            <div class="empty-state">Belum ada sub bab pada materi ini.</div>
        @else
            <div class="subsection-grid">
                @foreach ($subsections as $subsection)
                    <article class="subsection-item card-mode">
                        <div class="subsection-content">
                            <span class="subject-badge">Sub Bab {{ $subsection->position }}</span>
                            <h3>{{ $subsection->title }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($subsection->description), 140) }}</p>
                            @if ($role === 'siswa')
                                <span class="status-badge {{ $subsection->is_completed ? 'done' : 'pending' }}">
                                    {{ $subsection->is_completed ? 'Sudah Dibaca' : 'Belum Dibaca' }}
                                </span>
                            @else
                                <p class="material-summary">{{ $subsection->completed_students_count }} siswa sudah membaca sub bab ini.</p>
                            @endif
                        </div>

                        <div class="subsection-actions">
                            <a class="btn btn-soft" href="{{ route('materials.subsections.show', [$subject, $material, $subsection]) }}">
                                {{ $role === 'siswa' ? 'Buka Sub Bab' : 'Lihat Detail' }}
                            </a>
                            @if ($role === 'guru')
                                <a class="btn btn-soft" href="{{ route('guru.materials.subsections.edit', [$subject, $material, $subsection]) }}">Edit</a>
                                <form method="POST" action="{{ route('guru.materials.subsections.destroy', [$subject, $material, $subsection]) }}" onsubmit="return confirm('Hapus sub bab ini?');">
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
