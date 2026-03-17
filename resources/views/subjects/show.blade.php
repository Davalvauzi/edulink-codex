@extends('layouts.portal')

@section('sidebar')
    <a href="{{ route($role.'.dashboard') }}">
        Kembali ke Dashboard
        <span>{{ ucfirst($role) }}</span>
    </a>
    <div class="static-item">
        Mata Pelajaran
        <span>{{ $subject->name }} kelas {{ $subject->kelas }}</span>
    </div>
    <div class="static-item">
        Total Materi
        <span>{{ $subject->materials->count() }} materi tersedia</span>
    </div>
@endsection

@section('heading', $subject->name)
@section('subtitle', 'Halaman mata pelajaran ini menampilkan daftar materi. Setiap materi memiliki halaman detail sendiri, dan guru dapat menambah materi dari halaman terpisah.')

@section('actions')
    @if ($role === 'guru')
        <a class="btn btn-primary" href="{{ route('guru.subjects.materials.create', $subject) }}">Tambah Materi</a>
    @endif
    <a class="btn btn-soft" href="{{ route($role.'.dashboard') }}">Kembali</a>
@endsection

@section('content')
    <section class="cards">
        <article class="card">
            <strong>Kelas</strong>
            <p>Mata pelajaran ini tersedia untuk kelas {{ $subject->kelas }}.</p>
        </article>
        <article class="card">
            <strong>Pembuat</strong>
            <p>{{ $subject->creator?->name ?? 'Guru tidak diketahui' }}</p>
        </article>
        <article class="card">
            <strong>Total Materi</strong>
            <p>{{ $subject->materials->count() }} bab atau materi sudah ditambahkan.</p>
        </article>
    </section>

    <section class="meta">
        <div class="section-title">
            <div>
                <strong>Daftar Materi</strong>
                <p>Klik salah satu materi untuk membuka halaman detail materi.</p>
            </div>
        </div>

        @if ($subject->materials->isEmpty())
            <div class="empty-state">Belum ada materi pada mata pelajaran ini.</div>
        @else
            <div class="materials-grid">
                @foreach ($subject->materials as $material)
                    <a class="material-item" href="{{ route('materials.show', [$subject, $material]) }}">
                        <span class="subject-badge">{{ $material->title }}</span>
                        <h3>{{ $material->title }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($material->description), 140) }}</p>

                        <div class="material-meta">
                            <div>
                                <span>Diunggah oleh</span>
                                <strong>{{ $material->creator?->name ?? 'Guru tidak diketahui' }}</strong>
                            </div>
                            <div>
                                <span>Tanggal</span>
                                <strong>{{ $material->created_at?->format('d M Y H:i') }}</strong>
                            </div>
                            <div>
                                <span>File</span>
                                <strong>{{ $material->file_name ?? 'Tidak ada file' }}</strong>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
