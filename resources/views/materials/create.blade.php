@extends('layouts.portal')

@section('sidebar')
    <a href="{{ route('subjects.show', $subject) }}">
        Kembali ke Mata Pelajaran
        <span>{{ $subject->name }}</span>
    </a>
    <div class="static-item">
        Tambah Materi
        <span>Buat bab baru untuk {{ strtolower($subject->kelasLabel()) }}</span>
    </div>
@endsection

@section('heading', 'Tambah Materi Baru')
@section('subtitle', 'Gunakan halaman ini untuk menambah materi agar proses input lebih rapi. Deskripsi mendukung bold, italic, heading, list, dan quote.')

@section('actions')
    <a class="btn btn-soft" href="{{ route('subjects.show', $subject) }}">Kembali</a>
@endsection

@section('content')
    <section class="meta">
        <div class="section-title">
            <div>
                <strong>{{ $subject->name }}</strong>
                <p>Tambahkan materi baru untuk mata pelajaran ini.</p>
            </div>
        </div>

        <form class="material-form" method="POST" action="{{ route('guru.subjects.materials.store', $subject) }}" enctype="multipart/form-data">
            @csrf
            @include('materials._form', ['material' => null, 'editorId' => 'create-material-editor', 'inputId' => 'material-description'])
            <button class="btn btn-primary" type="submit">Simpan Materi</button>
        </form>
    </section>
@endsection
