@extends('layouts.portal')

@section('sidebar')
    <div class="static-item">
        Halaman Aktif
        <span>Materi</span>
    </div>
    @if ($role === 'siswa' && isset($selectedKelas))
        <div class="static-item">
            Kelas
            <span>Menampilkan kelas {{ $selectedKelas }}</span>
        </div>
    @endif
@endsection

@section('heading', 'Materi')
@section('subtitle', 'Halaman ini berisi pintasan ke semua mata pelajaran dan materi. Gunakan sidebar untuk berpindah cepat antara dashboard, materi, dan kuis.')

@section('actions')
    @if ($role === 'guru')
        <a class="btn btn-primary" href="{{ route('guru.subjects.create') }}">Tambah Mata Pelajaran</a>
    @endif
@endsection

@section('content')
    @if ($role === 'siswa')
        <section class="meta">
            <form class="filter-form" method="GET" action="{{ route('siswa.materials') }}">
                <div class="field">
                    <label for="kelas-filter-materials">Filter Kelas</label>
                    <select id="kelas-filter-materials" name="kelas">
                        <option value="10" @selected(($selectedKelas ?? null) === '10')>Kelas 10</option>
                        <option value="11" @selected(($selectedKelas ?? null) === '11')>Kelas 11</option>
                        <option value="12" @selected(($selectedKelas ?? null) === '12')>Kelas 12</option>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Terapkan Filter</button>
            </form>
        </section>
    @endif

    <section class="meta">
        <div class="section-title">
            <div>
                <strong>Daftar Mata Pelajaran</strong>
                <p>Pilih mapel untuk membuka halaman bab dan sub bab yang ada di dalamnya.</p>
            </div>
        </div>

        @if ($subjects->isEmpty())
            <div class="empty-state">
                Belum ada mata pelajaran yang bisa ditampilkan.
                <div style="margin-top: 14px;">
                    <a class="btn btn-soft" href="{{ $emptyActionRoute }}">{{ $emptyActionLabel }}</a>
                </div>
            </div>
        @else
            <div class="subjects-grid">
                @foreach ($subjects as $subject)
                    <a class="subject-item" href="{{ route('subjects.show', $subject) }}">
                        <span class="subject-badge">Kelas {{ $subject->kelas }}</span>
                        <h3>{{ $subject->name }}</h3>
                        <p>{{ $subject->materials_count }} materi tersedia. Klik untuk membuka daftar materi.</p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
