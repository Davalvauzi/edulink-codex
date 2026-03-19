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
        <a class="btn btn-primary" href="{{ route('guru.materials.subsections.create', [$subject, $material]) }}">Tambah Sub Bab</a>
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
    <section class="cards">
        <article class="card">
            <strong>Mata Pelajaran</strong>
            <p>{{ $subject->name }}</p>
        </article>
        <article class="card">
            <strong>Dibuat Oleh</strong>
            <p>{{ $material->creator?->name ?? 'Guru tidak diketahui' }}</p>
        </article>
        <article class="card">
            <strong>Terakhir Diperbarui</strong>
            <p>{{ $material->updated_at?->format('d M Y H:i') }}</p>
        </article>
        <article class="card">
            <strong>Total Sub Bab</strong>
            <p>{{ $totalSubsections }} sub bab dalam materi ini.</p>
        </article>
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
                <strong>Daftar Sub Bab</strong>
                <p>{{ $role === 'guru' ? 'Kelola pembahasan bertahap di bawah bab utama ini.' : 'Buka sub bab untuk membaca isi materi sekaligus menambah progress belajar.' }}</p>
            </div>
        </div>

        @if ($subsections->isEmpty())
            <div class="empty-state">Belum ada sub bab pada materi ini.</div>
        @else
            <div class="subsection-list">
                @foreach ($subsections as $subsection)
                    <article class="subsection-item">
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
