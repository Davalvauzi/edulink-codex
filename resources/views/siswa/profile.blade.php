@extends('layouts.portal')

@section('sidebar')
    <a href="{{ route('siswa.dashboard') }}">
        Dashboard Siswa
        <span>Kembali ke daftar mata pelajaran</span>
    </a>
    <div class="static-item">
        Profil Aktif
        <span>{{ $user->name }}</span>
    </div>
    <div class="static-item">
        Kelas
        <span>Kelas {{ $user->kelas }}</span>
    </div>
@endsection

@section('heading', 'Profil Siswa')
@section('subtitle', 'Perbarui data akun Anda dari halaman ini. Tombol profil di kanan atas dashboard sekarang akan mengarah ke halaman khusus ini.')

@section('actions')
    <a class="btn btn-soft" href="{{ route('siswa.dashboard') }}">Kembali ke Dashboard</a>
@endsection

@section('content')
    <section class="meta">
        <div class="section-title">
            <div>
                <strong>Perbarui Profil</strong>
                <p>Anda bisa mengubah nama, email, kelas, dan password dari satu tempat.</p>
            </div>
        </div>

        <form class="profile-form" method="POST" action="{{ route('siswa.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="name">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="field">
                <label for="kelas">Kelas</label>
                <select id="kelas" name="kelas" required>
                    <option value="10" @selected(old('kelas', $user->kelas) === '10')>10</option>
                    <option value="11" @selected(old('kelas', $user->kelas) === '11')>11</option>
                    <option value="12" @selected(old('kelas', $user->kelas) === '12')>12</option>
                </select>
            </div>

            <div class="field">
                <label for="password">Password Baru</label>
                <input id="password" type="password" name="password" placeholder="Kosongkan jika tidak diubah">
            </div>

            <div class="field">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation">
            </div>

            <div class="field field-full">
                <button class="btn btn-primary" type="submit">Simpan Profil</button>
            </div>
        </form>
    </section>

    <section class="meta">
        <strong>Ringkasan Akun</strong>
        <div class="meta-grid">
            <div class="meta-item">
                <span>Nama</span>
                <strong>{{ $user->name }}</strong>
            </div>
            <div class="meta-item">
                <span>Email</span>
                <strong>{{ $user->email }}</strong>
            </div>
            <div class="meta-item">
                <span>Kelas</span>
                <strong>{{ $user->kelas }}</strong>
            </div>
        </div>
    </section>
@endsection
