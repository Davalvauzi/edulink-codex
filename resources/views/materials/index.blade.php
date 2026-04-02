@extends('layouts.portal')

@section('sidebar')
    <div class="static-item">
        Halaman Aktif
        <span>Materi</span>
    </div>
@endsection

@section('heading', 'Materi')
@section('subtitle', 'Halaman ini berisi pintasan ke semua mata pelajaran dan materi. Gunakan sidebar untuk berpindah cepat antara dashboard, materi, dan kuis.')

@section('actions')
    @if ($role === 'guru')
        <a class="btn btn-primary" href="{{ route('guru.subjects.create') }}">Tambah Mata Pelajaran</a>
    @endif
@endsection

@section('content')
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
                        <span class="subject-badge">{{ $subject->kelasLabel() }}</span>
                        <h3>{{ $subject->name }}</h3>
                        <p>{{ $subject->materials_count }} materi tersedia. Klik untuk membuka daftar materi.</p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
