@extends('layouts.portal')

@section('sidebar')
    <div class="static-item">
        Dashboard {{ ucfirst($role) }}
        <span>{{ $title }}</span>
    </div>
    <div class="static-item">
        Status Akun
        <span>Role aktif: {{ $role }}</span>
    </div>
    @if ($role === 'siswa' && isset($user))
        <div class="static-item">
            Kelas Aktif
            <span>Kelas {{ $user->kelas }}</span>
        </div>
        <div class="static-item">
            Filter Materi
            <span>Menampilkan kelas {{ $selectedKelas }}</span>
        </div>
    @endif
    @if ($role === 'guru')
        <div class="static-item">
            Kelola Materi
            <span>Klik mata pelajaran untuk membuka daftar materi</span>
        </div>
    @endif
@endsection

@section('heading')
    @if ($role === 'siswa' && isset($user))
        Halo {{ $user->name }}
    @else
        {{ $title }}
    @endif
@endsection

@section('subtitle', $message)

@section('actions')
    @if ($role === 'siswa' && isset($user))
        <a class="btn btn-soft" href="{{ route('siswa.profile') }}">Profile</a>
    @endif
@endsection

@section('content')
    @if ($role === 'siswa' && isset($user))
        <section class="cards">
            <article class="card">
                <strong>Ringkasan Akademik</strong>
                <p>Pantau perkembangan belajar Anda dan buka materi per mata pelajaran dari dashboard ini.</p>
            </article>
            <article class="card">
                <strong>Informasi Kelas</strong>
                <p>Anda terdaftar sebagai siswa kelas {{ $user->kelas }} dan dashboard otomatis menampilkan daftar mapel terkait.</p>
            </article>
            <article class="card">
                <strong>Halaman Profil Baru</strong>
                <p>Tombol profile di kanan atas sekarang membuka halaman khusus untuk mengubah data siswa.</p>
            </article>
        </section>

        <section class="meta">
            <div class="section-title">
                <div>
                    <strong>Mata Pelajaran Berdasarkan Kelas</strong>
                    <p>Gunakan filter kelas lalu klik mata pelajaran untuk melihat materi yang tersedia.</p>
                </div>
            </div>

            <form class="filter-form" method="GET" action="{{ route('siswa.dashboard') }}">
                <div class="field">
                    <label for="kelas-filter">Filter Kelas</label>
                    <select id="kelas-filter" name="kelas">
                        <option value="10" @selected($selectedKelas === '10')>Kelas 10</option>
                        <option value="11" @selected($selectedKelas === '11')>Kelas 11</option>
                        <option value="12" @selected($selectedKelas === '12')>Kelas 12</option>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Terapkan Filter</button>
            </form>

            @if ($subjects->isEmpty())
                <div class="empty-state">Belum ada mata pelajaran untuk kelas {{ $selectedKelas }}.</div>
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
    @elseif ($role === 'guru')
        <section class="meta">
            <div class="section-title">
                <div>
                    <strong>Tambah Mata Pelajaran</strong>
                    <p>Setelah dibuat, klik salah satu mata pelajaran untuk menambahkan materi seperti Bab 1, Bab 2, dan file PDF.</p>
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
                <button class="btn btn-primary" type="submit">Tambah Mata Pelajaran</button>
            </form>

            @if ($subjects->isEmpty())
                <div class="empty-state">Belum ada mata pelajaran yang tersimpan.</div>
            @else
                <div class="subjects-grid">
                    @foreach ($subjects as $subject)
                        <a class="subject-item" href="{{ route('subjects.show', $subject) }}">
                            <span class="subject-badge">Kelas {{ $subject->kelas }}</span>
                            <h3>{{ $subject->name }}</h3>
                            <p>{{ $subject->materials_count }} materi tersimpan. Klik untuk membuka dan menambah materi baru.</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    @else
        <section class="meta">
            <strong>Ringkasan Dashboard</strong>
            <div class="meta-grid">
                <div class="meta-item">
                    <span>Role</span>
                    <strong>{{ ucfirst($role) }}</strong>
                </div>
                <div class="meta-item">
                    <span>Akses</span>
                    <strong>Aktif</strong>
                </div>
                <div class="meta-item">
                    <span>Portal</span>
                    <strong>EduLink Codex</strong>
                </div>
            </div>
        </section>
    @endif
@endsection
