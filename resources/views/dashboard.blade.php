@extends('layouts.portal')

@section('sidebar')
    <div class="static-item">
        Dashboard Aktif
        <span>{{ ucfirst($role) }}</span>
    </div>
    @if ($role === 'siswa' && isset($selectedKelas))
        <div class="static-item">
            Filter Kelas
            <span>Menampilkan {{ \App\Models\User::kelasLabel($selectedKelas) }}</span>
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
    @if ($role === 'guru')
        <a class="btn btn-primary" href="{{ route('guru.subjects.create') }}">Tambah Mata Pelajaran</a>
    @endif
    @if ($role === 'siswa' && isset($user))
        <a class="btn btn-soft" href="{{ route('siswa.profile') }}">Profile</a>
    @endif
@endsection

@section('content')
    <section class="cards">
        @foreach ($dashboardStats ?? [] as $stat)
            <article class="card">
                <strong>{{ $stat['label'] }}</strong>
                <h2 style="margin: 0 0 8px; font-size: 30px;">{{ $stat['value'] }}</h2>
                <p>{{ $stat['detail'] }}</p>
            </article>
        @endforeach
    </section>

    @if ($role === 'siswa' && isset($totalSubsections))
        <section class="meta">
            <div class="section-title">
                <div>
                    <strong>Progress Pembelajaran</strong>
                    <p>Progress belajar siswa diringkas dari sub bab yang sudah dibuka pada kelas yang sedang dipilih.</p>
                </div>
            </div>

            <div class="progress-panel">
                <div>
                    <strong>{{ $completedSubsections }} dari {{ $totalSubsections }} sub bab selesai</strong>
                    <p>Progress keseluruhan untuk {{ strtolower(\App\Models\User::kelasLabel($selectedKelas)) }} saat ini {{ $progressPercentage }}%.</p>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" style="width: {{ $progressPercentage }}%;"></div>
                </div>
                <span class="progress-value">{{ $progressPercentage }}%</span>
            </div>
        </section>
    @endif

    {{-- <section class="meta">
        <div class="section-title">
            <div>
                <strong>Fokus Dashboard</strong>
                <p>Informasi yang dulu tersebar di sidebar sekarang diringkas di dashboard agar halaman lain lebih bersih.</p>
            </div>
        </div>

        <div class="subjects-grid">
            @foreach ($progressHighlights ?? [] as $highlight)
                <article class="subject-item">
                    <h3>{{ $highlight['title'] }}</h3>
                    <p>{{ $highlight['description'] }}</p>
                </article>
            @endforeach
        </div>
    </section> --}}

    @if ($role === 'siswa' && isset($user))
        {{-- <section class="meta">
            <div class="section-title">
                <div>
                    <strong>Mata Pelajaran Berdasarkan Kelas</strong>
                    <p>Gunakan filter kelas lalu buka menu Materi untuk melihat semua bab secara lebih fokus.</p>
                </div>
            </div>

            <form class="filter-form" method="GET" action="{{ route('siswa.dashboard') }}">
                <div class="field">
                    <label for="kelas-filter">Filter Kelas</label>
                    <select id="kelas-filter" name="kelas">
                        @foreach (\App\Models\User::kelasOptions() as $kelasValue => $kelasLabel)
                            <option value="{{ $kelasValue }}" @selected($selectedKelas === $kelasValue)>{{ $kelasLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Terapkan Filter</button>
            </form>

            @if (($subjects ?? collect())->isEmpty())
                <div class="empty-state">Belum ada mata pelajaran untuk {{ strtolower(\App\Models\User::kelasLabel($selectedKelas)) }}.</div>
            @else
                <div class="subjects-grid">
                    @foreach ($subjects as $subject)
                        <a class="subject-item" href="{{ route('subjects.show', $subject) }}">
                            <span class="subject-badge">{{ $subject->kelasLabel() }}</span>
                            <h3>{{ $subject->name }}</h3>
                            <p>{{ $subject->materials_count }} materi tersedia. Buka mapel ini dari halaman Materi atau
                                langsung dari sini.</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </section> --}}
    @elseif ($role === 'guru')
        {{-- <section class="meta">
            <div class="section-title">
                <div>
                    <strong>Mapel Terbaru</strong>
                    <p>Dashboard guru menampilkan progres konten, sementara detail lengkap bisa dibuka dari menu Materi dan
                        Kuis.</p>
                </div>
            </div>

            @if (($subjects ?? collect())->isEmpty())
                <div class="empty-state">Belum ada mata pelajaran yang tersimpan.</div>
            @else
                <div class="subjects-grid">
                    @foreach ($subjects as $subject)
                        <a class="subject-item" href="{{ route('subjects.show', $subject) }}">
                            <span class="subject-badge">{{ $subject->kelasLabel() }}</span>
                            <h3>{{ $subject->name }}</h3>
                            <p>{{ $subject->materials_count }} materi dan {{ $subject->material_subsections_count }} sub
                                bab sudah tersedia.</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </section> --}}

        {{-- <section class="meta">
            <div class="section-title">
                <div>
                    <strong>Kuis Terbaru</strong>
                    <p>Daftar ini membantu guru memantau kuis yang baru dibuat sebelum membuka halaman Kuis penuh.</p>
                </div>
            </div>

            @if (($recentQuizzes ?? collect())->isEmpty())
                <div class="empty-state">Belum ada kuis yang dibuat pada materi.</div>
            @else
                <div class="quiz-grid">
                    @foreach ($recentQuizzes as $quiz)
                        <a class="subject-item"
                            href="{{ route('quizzes.show', [$quiz->material->subject, $quiz->material, $quiz]) }}">
                            <span class="subject-badge">{{ $quiz->material->subject->name }}</span>
                            <h3>{{ $quiz->title }}</h3>
                            <p>{{ $quiz->questions_count }} soal dan {{ $quiz->attempts_count }} attempt siswa.</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </section> --}}
    @endif
@endsection
