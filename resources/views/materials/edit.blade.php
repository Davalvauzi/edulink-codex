@extends('layouts.portal')

@section('sidebar')
    <a href="{{ route('materials.show', [$subject, $material]) }}">
        Kembali ke Detail Materi
        <span>{{ $material->title }}</span>
    </a>
    <div class="static-item">
        Edit Materi
        <span>{{ $subject->name }}</span>
    </div>
@endsection

@section('heading', 'Edit Materi')
@section('subtitle', 'Perbarui judul, deskripsi, atau file PDF materi dari halaman khusus ini.')

@section('actions')
    <a class="btn btn-soft" href="{{ route('materials.show', [$subject, $material]) }}">Batal</a>
@endsection

@section('content')
    <section class="meta">
        <div class="section-title">
            <div>
                <strong>{{ $material->title }}</strong>
                <p>Perubahan akan langsung terlihat pada halaman detail materi.</p>
            </div>
        </div>

        <form class="material-form" method="POST" action="{{ route('guru.materials.update', [$subject, $material]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('materials._form', ['material' => $material, 'editorId' => 'edit-material-editor', 'inputId' => 'material-description'])
            <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
        </form>
    </section>
@endsection
