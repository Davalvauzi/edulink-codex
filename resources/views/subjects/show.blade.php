@extends('layouts.portal')

@section('sidebar')
    <a href="{{ route($role.'.dashboard') }}">
        Kembali ke Dashboard
        <span>{{ ucfirst($role) }}</span>
    </a>
    <div class="static-item">
        Mata Pelajaran
        <span>{{ $subject->name }} kelas {{ $subject->kelas }}</span>
    </div>
    <div class="static-item">
        Total Materi
        <span>{{ $subject->materials->count() }} materi tersedia</span>
    </div>
@endsection

@section('heading', $subject->name)
@section('subtitle', 'Kelola dan lihat daftar materi untuk mata pelajaran ini. Setiap materi dapat berisi judul bab, deskripsi, dan file PDF pendukung.')

@section('actions')
    <a class="btn btn-soft" href="{{ route($role.'.dashboard') }}">Kembali</a>
@endsection

@section('content')
    <section class="cards">
        <article class="card">
            <strong>Kelas</strong>
            <p>Mata pelajaran ini tersedia untuk kelas {{ $subject->kelas }}.</p>
        </article>
        <article class="card">
            <strong>Pembuat</strong>
            <p>{{ $subject->creator?->name ?? 'Guru tidak diketahui' }}</p>
        </article>
        <article class="card">
            <strong>Total Materi</strong>
            <p>{{ $subject->materials->count() }} bab atau materi sudah ditambahkan.</p>
        </article>
    </section>

    @if ($role === 'guru')
        <section class="meta">
            <div class="section-title">
                <div>
                    <strong>Tambah Materi Baru</strong>
                    <p>Isi nama materi seperti Bab 1, Bab 2, dan tambahkan file PDF jika tersedia.</p>
                </div>
            </div>

            <form class="material-form" method="POST" action="{{ route('guru.subjects.materials.store', $subject) }}" enctype="multipart/form-data">
                @csrf
                <div class="field">
                    <label for="title">Nama Materi</label>
                    <input id="title" type="text" name="title" value="{{ old('title') }}" placeholder="Contoh: Bab 1" required>
                </div>
                <div class="field field-full">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" placeholder="Jelaskan isi materi ini" required>{{ old('description') }}</textarea>
                </div>
                <div class="field">
                    <label for="file">File PDF</label>
                    <input id="file" type="file" name="file" accept="application/pdf">
                </div>
                <button class="btn btn-primary" type="submit">Simpan Materi</button>
            </form>
        </section>
    @endif

    <section class="meta">
        <div class="section-title">
            <div>
                <strong>Daftar Materi</strong>
                <p>Materi terbaru ditampilkan di bagian paling atas.</p>
            </div>
        </div>

        @if ($subject->materials->isEmpty())
            <div class="empty-state">Belum ada materi pada mata pelajaran ini.</div>
        @else
            <div class="materials-grid">
                @foreach ($subject->materials as $material)
                    <article class="material-item">
                        <span class="subject-badge">{{ $material->title }}</span>
                        <h3>{{ $material->title }}</h3>
                        <p>{{ $material->description }}</p>

                        <div class="material-meta">
                            <div>
                                <span>Diunggah oleh</span>
                                <strong>{{ $material->creator?->name ?? 'Guru tidak diketahui' }}</strong>
                            </div>
                            <div>
                                <span>Tanggal</span>
                                <strong>{{ $material->created_at?->format('d M Y H:i') }}</strong>
                            </div>
                            <div>
                                <span>File</span>
                                @if ($material->file_path)
                                    <a class="link-inline" href="{{ asset('storage/'.$material->file_path) }}" target="_blank" rel="noopener">
                                        {{ $material->file_name ?? 'Lihat PDF' }}
                                    </a>
                                @else
                                    <strong>Tidak ada file</strong>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
