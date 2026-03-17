<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        :root {
            --bg: #f3f7f6;
            --surface: #ffffff;
            --sidebar: #18342f;
            --sidebar-soft: #28574f;
            --text: #10231f;
            --muted: #5a6f69;
            --line: #d7e4df;
            --accent: #d97706;
            --accent-soft: #fff1d6;
            --success-bg: #dcfce7;
            --success-text: #166534;
            --warn-bg: #fff7ed;
            --warn-text: #9a3412;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Trebuchet MS", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top right, rgba(217, 119, 6, 0.12), transparent 25%),
                linear-gradient(160deg, #eef6f3 0%, #f8fafc 45%, #eefbf6 100%);
            color: var(--text);
        }

        .layout {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 280px 1fr;
        }

        .sidebar {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.03), transparent 24%),
                var(--sidebar);
            color: #eefbf6;
            padding: 28px;
        }

        .brand {
            margin-bottom: 28px;
        }

        .brand h2 {
            margin: 0 0 8px;
            font-size: 26px;
            letter-spacing: 0.04em;
        }

        .brand p,
        .nav p {
            margin: 0;
            color: rgba(238, 251, 246, 0.72);
            line-height: 1.6;
        }

        .nav {
            margin-top: 26px;
            display: grid;
            gap: 14px;
        }

        .nav a,
        .nav .static-item {
            display: block;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
        }

        .nav .static-item span {
            display: block;
            margin-top: 4px;
            color: rgba(238, 251, 246, 0.7);
            font-size: 13px;
            font-weight: 400;
        }

        .main {
            padding: 28px;
        }

        .alert {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 16px;
        }

        .alert.error {
            background: var(--warn-bg);
            color: var(--warn-text);
        }

        .alert.success {
            background: var(--success-bg);
            color: var(--success-text);
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 26px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: #9a3412;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        h1 {
            margin: 14px 0 10px;
            font-size: 40px;
            line-height: 1.1;
        }

        .subtitle {
            margin: 0;
            color: var(--muted);
            max-width: 720px;
            line-height: 1.7;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        details.profile-menu {
            position: relative;
        }

        details.profile-menu summary {
            list-style: none;
            cursor: pointer;
            border: 0;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 12px 16px;
            font-weight: 700;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
        }

        details.profile-menu summary::-webkit-details-marker {
            display: none;
        }

        .profile-panel {
            position: absolute;
            right: 0;
            top: calc(100% + 12px);
            width: min(92vw, 360px);
            padding: 18px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: 0 28px 70px rgba(15, 23, 42, 0.14);
            z-index: 2;
        }

        .profile-panel h3 {
            margin: 0 0 12px;
            font-size: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
        }

        input, select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: #fff;
            margin-bottom: 14px;
            font-size: 14px;
        }

        .btn, button {
            border: 0;
            border-radius: 14px;
            padding: 12px 16px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, #d97706, #b45309);
        }

        .btn-dark {
            color: #fff;
            background: #10231f;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-top: 24px;
        }

        .card {
            background: rgba(255, 255, 255, 0.84);
            border: 1px solid rgba(215, 228, 223, 0.95);
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.05);
        }

        .card strong {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.7;
        }

        .meta {
            margin-top: 24px;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 24px;
        }

        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
        }

        .section-title p {
            margin: 0;
            color: var(--muted);
        }

        .filter-form,
        .subject-form {
            display: flex;
            gap: 12px;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-form .field,
        .subject-form .field {
            min-width: 160px;
            flex: 1;
        }

        .filter-form input,
        .filter-form select,
        .subject-form input,
        .subject-form select {
            margin-bottom: 0;
        }

        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-top: 18px;
        }

        .subject-item {
            padding: 20px;
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(244, 247, 246, 0.98));
            border: 1px solid var(--line);
        }

        .subject-badge {
            display: inline-block;
            margin-bottom: 12px;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: #9a3412;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.06em;
        }

        .subject-item h3 {
            margin: 0 0 8px;
            font-size: 20px;
        }

        .subject-item p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .empty-state {
            margin-top: 18px;
            padding: 22px;
            border-radius: 20px;
            background: #f8fbfa;
            border: 1px dashed var(--line);
            color: var(--muted);
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-top: 18px;
        }

        .meta-item {
            padding: 18px;
            border-radius: 18px;
            background: #f8fbfa;
            border: 1px solid var(--line);
        }

        .meta-item span {
            display: block;
            color: var(--muted);
            margin-bottom: 8px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .meta-item strong {
            font-size: 18px;
        }

        @media (max-width: 980px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .cards,
            .subjects-grid,
            .meta-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .main,
            .sidebar {
                padding: 20px;
            }

            .topbar {
                flex-direction: column;
            }

            .actions {
                width: 100%;
                justify-content: flex-start;
            }

            h1 {
                font-size: 32px;
            }

            .section-title,
            .filter-form,
            .subject-form {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <h2>EduLink</h2>
                <p>Portal sekolah dengan akses berbasis peran untuk admin, guru, dan siswa.</p>
            </div>

            <div class="nav">
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
                        <span>Tambah mapel untuk kelas 10, 11, dan 12</span>
                    </div>
                @endif
            </div>
        </aside>

        <main class="main">
            @if (session('error'))
                <div class="alert error">{{ session('error') }}</div>
            @endif

            @if (session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            <div class="topbar">
                <div>
                    <span class="eyebrow">{{ $role }}</span>
                    @if ($role === 'siswa' && isset($user))
                        <h1>Halo {{ $user->name }}</h1>
                    @else
                        <h1>{{ $title }}</h1>
                    @endif
                    <p class="subtitle">{{ $message }}</p>
                </div>

                <div class="actions">
                    @if ($role === 'siswa' && isset($user))
                        <details class="profile-menu">
                            <summary>Edit Profile</summary>
                            <div class="profile-panel">
                                <h3>Perbarui Profil Siswa</h3>
                                <form method="POST" action="{{ route('siswa.profile.update') }}">
                                    @csrf
                                    @method('PUT')

                                    <label for="name">Nama</label>
                                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>

                                    <label for="email">Email</label>
                                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>

                                    <label for="kelas">Kelas</label>
                                    <select id="kelas" name="kelas" required>
                                        <option value="10" @selected(old('kelas', $user->kelas) === '10')>10</option>
                                        <option value="11" @selected(old('kelas', $user->kelas) === '11')>11</option>
                                        <option value="12" @selected(old('kelas', $user->kelas) === '12')>12</option>
                                    </select>

                                    <label for="password">Password Baru</label>
                                    <input id="password" type="password" name="password" placeholder="Kosongkan jika tidak diubah">

                                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                                    <input id="password_confirmation" type="password" name="password_confirmation">

                                    <button class="btn btn-primary" type="submit">Simpan Profil</button>
                                </form>
                            </div>
                        </details>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-dark" type="submit">Logout</button>
                    </form>
                </div>
            </div>

            @if ($role === 'siswa' && isset($user))
                <section class="cards">
                    <article class="card">
                        <strong>Ringkasan Akademik</strong>
                        <p>Pantau perkembangan belajar Anda, lihat jadwal penting, dan pastikan data pribadi selalu terbarui.</p>
                    </article>
                    <article class="card">
                        <strong>Informasi Kelas</strong>
                        <p>Anda terdaftar sebagai siswa kelas {{ $user->kelas }} dan dashboard otomatis menampilkan materi untuk kelas tersebut.</p>
                    </article>
                    <article class="card">
                        <strong>Filter Manual</strong>
                        <p>Anda tetap bisa memeriksa materi kelas 10, 11, atau 12 secara manual menggunakan filter di bawah.</p>
                    </article>
                </section>

                <section class="meta">
                    <div class="section-title">
                        <div>
                            <strong>Materi Berdasarkan Kelas</strong>
                            <p>Default filter mengikuti kelas siswa. Ganti filter untuk melihat materi dari kelas lain.</p>
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
                                <article class="subject-item">
                                    <span class="subject-badge">Kelas {{ $subject->kelas }}</span>
                                    <h3>{{ $subject->name }}</h3>
                                    <p>Materi ini tersedia untuk siswa kelas {{ $subject->kelas }} di portal EduLink.</p>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="meta">
                    <strong>Profil Siswa</strong>
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
            @elseif ($role === 'guru')
                <section class="meta">
                    <div class="section-title">
                        <div>
                            <strong>Tambah Mata Pelajaran</strong>
                            <p>Guru dapat menambahkan mata pelajaran baru lalu menentukan mapel tersebut untuk kelas 10, 11, atau 12.</p>
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
                                <article class="subject-item">
                                    <span class="subject-badge">Kelas {{ $subject->kelas }}</span>
                                    <h3>{{ $subject->name }}</h3>
                                    <p>Ditambahkan ke daftar materi kelas {{ $subject->kelas }}.</p>
                                </article>
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
        </main>
    </div>
</body>
</html>
