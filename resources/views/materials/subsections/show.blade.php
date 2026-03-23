@extends('layouts.portal')

@section('sidebar')
    <a href="{{ route('materials.show', [$subject, $material]) }}">
        Kembali ke Materi
        <span>{{ $material->title }}</span>
    </a>
    <div class="static-item">
        Sub Bab Aktif
        <span>{{ $subsection->title }}</span>
    </div>
    <div class="static-item">
        Mata Pelajaran
        <span>{{ $subject->name }}</span>
    </div>
@endsection

@section('heading', $subsection->title)
@section('subtitle', $role === 'siswa' ? 'Membuka sub bab ini akan dicatat sebagai progres belajar pada bab utama.' : 'Sub bab ini merupakan bagian dari materi utama dan tetap berada di bawah bab induk.')

@section('actions')
    @if ($role === 'siswa')
        <a class="btn btn-primary" href="{{ route('siswa.ai.index', ['subject' => $subject->id, 'material' => $material->id, 'subsection' => $subsection->id]) }}">Tanya AI</a>
    @endif
    @if ($role === 'guru')
        <a class="btn btn-soft" href="{{ route('guru.materials.subsections.edit', [$subject, $material, $subsection]) }}">Edit</a>
        <form method="POST" action="{{ route('guru.materials.subsections.destroy', [$subject, $material, $subsection]) }}" onsubmit="return confirm('Hapus sub bab ini?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger" type="submit">Hapus</button>
        </form>
    @endif
    <a class="btn btn-soft" href="{{ route('materials.show', [$subject, $material]) }}">Kembali</a>
@endsection

@section('content')
    <section class="cards">
        <article class="card">
            <strong>Bab Utama</strong>
            <p>{{ $material->title }}</p>
        </article>
        <article class="card">
            <strong>Urutan Sub Bab</strong>
            <p>Sub bab ke-{{ $subsection->position }}</p>
        </article>
        <article class="card">
            <strong>Progress Bab</strong>
            <p>{{ $completedSubsections }}/{{ $totalSubsections }} sub bab selesai ({{ $progressPercentage }}%).</p>
        </article>
    </section>

    <section class="meta stack">
        <div>
            <strong>Isi Sub Bab</strong>
            @if ($subsection->image_source)
                <div class="question-media" style="margin-top: 14px;">
                    <img src="{{ $subsection->image_source }}" alt="Gambar sub bab {{ $subsection->title }}">
                    <div class="question-media-caption">{{ $subsection->image_name ?: 'Gambar dari tautan eksternal' }}</div>
                </div>
            @endif
            <div class="prose">{!! $subsection->description !!}</div>
        </div>
    </section>

    @if ($role === 'siswa')
        <section class="meta">
            <div class="progress-panel">
                <div>
                    <strong>Progress Belajar Bab {{ $material->title }}</strong>
                    <p>Progress bertambah saat Anda membuka sub bab. Saat ini Anda sudah menyelesaikan {{ $completedSubsections }} dari {{ $totalSubsections }} sub bab.</p>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" style="width: {{ $progressPercentage }}%;"></div>
                </div>
                <span class="progress-value">{{ $progressPercentage }}%</span>
            </div>
        </section>
    @endif

    @if ($nextSubsection)
        <section class="meta compact">
            <div class="section-title">
                <div>
                    <strong>Lanjut Belajar</strong>
                    <p>Setelah selesai membaca sub bab ini, lanjut ke sub bab berikutnya tanpa kembali ke daftar materi.</p>
                </div>
                <a class="btn btn-primary btn-section" href="{{ route('materials.subsections.show', [$subject, $material, $nextSubsection]) }}">
                    Selanjutnya
                </a>
            </div>
        </section>
    @endif
@endsection
