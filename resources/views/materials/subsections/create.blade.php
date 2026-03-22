@extends('layouts.portal')

@section('sidebar')
    <a href="{{ route('materials.show', [$subject, $material]) }}">
        Kembali ke Detail Materi
        <span>{{ $material->title }}</span>
    </a>
    <div class="static-item">
        Tambah Sub Bab
        <span>{{ $subject->name }}</span>
    </div>
@endsection

@section('heading', 'Tambah Sub Bab')
@section('subtitle', 'Bab utama tetap menjadi induk materi. Gunakan halaman ini untuk menambahkan pembahasan bertahap dalam bentuk sub bab.')

@section('actions')
    <a class="btn btn-soft" href="{{ route('materials.show', [$subject, $material]) }}">Kembali</a>
@endsection

@section('content')
    <section class="meta">
        <div class="section-title">
            <div>
                <strong>{{ $material->title }}</strong>
                <p>Tambahkan sub bab baru agar materi bisa dipelajari bertahap oleh siswa.</p>
            </div>
        </div>

        <form class="material-form" method="POST" action="{{ route('guru.materials.subsections.store', [$subject, $material]) }}" enctype="multipart/form-data">
            @csrf
            @include('materials.subsections._form', ['subsection' => null, 'editorId' => 'create-subsection-editor', 'inputId' => 'subsection-description'])
            <button class="btn btn-primary" type="submit">Simpan Sub Bab</button>
        </form>
    </section>
@endsection
