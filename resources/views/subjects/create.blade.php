@extends('layouts.portal')

@section('sidebar')
    <a href="{{ route('guru.dashboard') }}">
        Dashboard Guru
        <span>Kembali ke daftar mata pelajaran</span>
    </a>
    <div class="static-item">
        Tambah Mata Pelajaran
        <span>Buat mapel baru untuk kelas 10, 11, atau 12</span>
    </div>
@endsection

@section('heading', 'Tambah Mata Pelajaran')
@section('subtitle', 'Form penambahan mata pelajaran dipisah ke halaman sendiri agar dashboard guru tetap rapi dan fokus.')

@section('actions')
    <a class="btn btn-soft" href="{{ route('guru.dashboard') }}">Kembali</a>
@endsection

@section('content')
    <section class="meta">
        <div class="section-title">
            <div>
                <strong>Form Mata Pelajaran Baru</strong>
                <p>Setelah tersimpan, Anda akan langsung masuk ke halaman mata pelajaran untuk mengelola materi.</p>
            </div>
        </div>

        <form class="subject-form" method="POST" action="{{ route('guru.subjects.store') }}">
            @csrf
            <div class="field">
                <label for="subject-name">Nama Mata Pelajaran</label>
                <input id="subject-name" type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Fisika" required>
            </div>
            <div class="field">
                <label for="subject-kelas">Kelas</label>
                <select id="subject-kelas" name="kelas" required>
                    <option value="">Pilih kelas</option>
                    <option value="10" @selected(old('kelas') === '10')>10</option>
                    <option value="11" @selected(old('kelas') === '11')>11</option>
                    <option value="12" @selected(old('kelas') === '12')>12</option>
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Simpan Mata Pelajaran</button>
        </form>
    </section>
@endsection
