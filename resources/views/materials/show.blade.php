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
    </section>

    <section class="meta stack">
        <div>
            <strong>Deskripsi Materi</strong>
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
@endsection
