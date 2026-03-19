@extends('layouts.portal')

@section('sidebar')
    <a href="{{ route('materials.subsections.show', [$subject, $material, $subsection]) }}">
        Kembali ke Sub Bab
        <span>{{ $subsection->title }}</span>
    </a>
    <div class="static-item">
        Edit Sub Bab
        <span>{{ $material->title }}</span>
    </div>
@endsection

@section('heading', 'Edit Sub Bab')
@section('subtitle', 'Perbarui urutan, judul, atau isi sub bab agar alur pembelajaran tetap jelas.')

@section('actions')
    <a class="btn btn-soft" href="{{ route('materials.subsections.show', [$subject, $material, $subsection]) }}">Batal</a>
@endsection

@section('content')
    <section class="meta">
        <div class="section-title">
            <div>
                <strong>{{ $subsection->title }}</strong>
                <p>Perubahan akan langsung tampil pada daftar sub bab materi utama.</p>
            </div>
        </div>

        <form class="material-form" method="POST" action="{{ route('guru.materials.subsections.update', [$subject, $material, $subsection]) }}">
            @csrf
            @method('PUT')
            @include('materials.subsections._form', ['subsection' => $subsection, 'editorId' => 'edit-subsection-editor', 'inputId' => 'subsection-description'])
            <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
        </form>
    </section>
@endsection
