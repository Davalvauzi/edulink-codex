@extends('layouts.portal')

@section('sidebar')
@endsection

@section('heading', $material->title)
@section('subtitle', 'Setiap materi sekarang memiliki halaman detail sendiri agar konten, file, dan pengelolaan materi lebih terstruktur.')

@section('actions')
    @if ($role === 'siswa')
        <a class="btn btn-primary" href="{{ route('siswa.ai.index', ['subject' => $subject->id, 'material' => $material->id]) }}">Tanya AI</a>
    @endif
    @if ($role === 'guru')
        <a class="btn btn-soft" href="{{ route('guru.materials.edit', [$subject, $material]) }}">Edit</a>
        <form method="POST" action="{{ route('guru.materials.destroy', [$subject, $material]) }}" onsubmit="return confirm('Hapus materi ini?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger" type="submit">Hapus</button>
        </form>
    @endif
    <a class="btn btn-soft" href="{{ route('subjects.show', $subject) }}">Kembali</a>
@endsection

@push('styles')
<style>
/* ── ANIMATIONS ── */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(18px) }
    to   { opacity: 1; transform: translateY(0) }
}
@keyframes fadeIn {
    from { opacity: 0 }
    to   { opacity: 1 }
}

/* ── HERO BANNER ── */
.hero {
    background: linear-gradient(135deg, var(--g600, #15803d) 0%, var(--t400, #0d9488) 100%);
    border-radius: 20px;
    padding: 36px 40px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    animation: fadeUp .45s ease both;
    box-shadow: 0 8px 32px rgba(21,128,61,.25);
}

.hero::before {
    content: '';
    position: absolute; right: -40px; top: -40px;
    width: 240px; height: 240px; border-radius: 50%;
    background: rgba(255,255,255,.07);
    pointer-events: none;
}
.hero::after {
    content: '';
    position: absolute; left: -30px; bottom: -50px;
    width: 180px; height: 180px; border-radius: 50%;
    background: rgba(255,255,255,.05);
    pointer-events: none;
}

.hero-title {
    font-size: 1.9rem; font-weight: 800;
    color: #fff; line-height: 1.2;
    margin-bottom: 10px;
    animation: fadeUp .45s .08s ease both;
}

.hero-desc {
    font-size: .875rem; color: rgba(255,255,255,.82);
    max-width: 560px; margin-bottom: 22px; line-height: 1.65;
    animation: fadeUp .45s .15s ease both;
}

.hero-meta {
    display: flex; flex-wrap: wrap; gap: 10px;
    animation: fadeUp .45s .22s ease both;
}

.hero-meta-item {
    display: flex; align-items: center; gap: 6px;
    font-size: .78rem; color: rgba(255,255,255,.92); font-weight: 600;
    background: rgba(255,255,255,.13);
    border: 1px solid rgba(255,255,255,.2);
    padding: 5px 12px; border-radius: 20px;
}

.hero-emoji {
    position: absolute; right: 40px; top: 50%;
    transform: translateY(-50%);
    font-size: 5.5rem; opacity: .15;
    pointer-events: none;
    animation: fadeIn .7s .25s ease both;
}

/* ── INFO CARDS ROW ── */
.info-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 22px;
    animation: fadeUp .45s .12s ease both;
}

.info-card {
    background: #fff;
    border: 1.5px solid #c4e8d1;
    border-radius: 16px;
    padding: 18px 20px;
    display: flex; align-items: flex-start; gap: 14px;
    transition: box-shadow .2s, border-color .2s, transform .2s;
}
.info-card:hover {
    border-color: #22c55e;
    box-shadow: 0 2px 20px rgba(21,128,61,.13);
    transform: translateY(-2px);
}

.info-card-icon {
    width: 42px; height: 42px; border-radius: 11px;
    background: linear-gradient(135deg,#ecfdf5,#d1fae5);
    border: 1.5px solid #bbf7d0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; flex-shrink: 0;
}

.info-card-body { flex: 1; min-width: 0 }

.info-card-label {
    font-size: .6rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: 1.2px;
    color: #3d6b4f; margin-bottom: 4px;
}

.info-card-val {
    font-size: .95rem; font-weight: 800;
    color: #0d1f14; line-height: 1.3;
}

.info-card-sub {
    font-size: .7rem; color: #3d6b4f; margin-top: 3px; font-weight: 500;
}

/* ── PROGRESS PANEL ── */
.progress-panel-new {
    background: #fff;
    border: 1.5px solid #c4e8d1;
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 22px;
    animation: fadeUp .45s .18s ease both;
}

.progress-panel-new .pp-head {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 10px;
}

.progress-panel-new .pp-title {
    font-size: .875rem; font-weight: 700; color: #0d1f14;
}

.progress-panel-new .pp-val {
    font-size: .875rem; font-weight: 800; color: #16a34a;
}

.progress-panel-new .pp-desc {
    font-size: .75rem; color: #3d6b4f; margin-bottom: 12px;
}

.pp-track {
    height: 8px; background: #e2f5ea; border-radius: 99px; overflow: hidden;
}
.pp-fill {
    height: 100%;
    background: linear-gradient(90deg, #22c55e, #0d9488);
    border-radius: 99px;
    transition: width .6s ease;
}

/* ── SEC HEADING ── */
.sec-head {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 16px;
}
.sec-head-line {
    width: 4px; height: 22px; border-radius: 3px;
    background: linear-gradient(180deg,#22c55e,#0d9488);
    flex-shrink: 0;
}
.sec-title {
    font-size: 1rem; font-weight: 800; color: #1e3a28;
}

/* ── DESCRIPTION SECTION ── */
.desc-section {
    background: #fff;
    border: 1.5px solid #c4e8d1;
    border-radius: 16px;
    padding: 22px 24px;
    margin-bottom: 18px;
    animation: fadeUp .45s .2s ease both;
}

.desc-section .prose {
    font-size: .875rem; line-height: 1.75; color: #1e3a28;
}

/* ── PDF SECTION ── */
.pdf-section {
    background: #fff;
    border: 1.5px solid #c4e8d1;
    border-radius: 16px;
    padding: 22px 24px;
    margin-bottom: 18px;
    animation: fadeUp .45s .23s ease both;
}

.pdf-list { display: flex; flex-direction: column; gap: 10px }

.pdf-item {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 16px;
    border-radius: 11px;
    border: 1.5px solid #e2f5ea;
    background: #f8fffe;
    text-decoration: none;
    transition: all .2s;
    position: relative;
    overflow: hidden;
}

.pdf-item::before {
    content: '';
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 3.5px;
    background: linear-gradient(180deg,#22c55e,#0d9488);
    border-radius: 3px;
}

.pdf-item:hover {
    border-color: #22c55e;
    background: #ecfdf5;
    box-shadow: 0 3px 16px rgba(21,128,61,.1);
    transform: translateX(4px);
}

.pdf-icon {
    width: 44px; height: 44px; border-radius: 11px;
    background: linear-gradient(135deg,#fee2e2,#fecaca);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem; flex-shrink: 0;
}

.pdf-info { flex: 1; min-width: 0 }

.pdf-name {
    font-size: .875rem; font-weight: 700; color: #0d1f14;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-bottom: 3px;
}

.pdf-meta {
    font-size: .7rem; color: #3d6b4f; font-weight: 500;
}

.pdf-action {
    display: flex; align-items: center; gap: 5px;
    font-size: .72rem; font-weight: 800; color: #15803d;
    background: #dcfce7; border: 1.5px solid #bbf7d0;
    border-radius: 8px; padding: 6px 12px; flex-shrink: 0;
    white-space: nowrap; transition: all .18s;
}
.pdf-item:hover .pdf-action {
    background: #16a34a; color: #fff; border-color: #16a34a;
}

/* ── QUIZ SECTION ── */
.quiz-section {
    background: #fff;
    border: 1.5px solid #c4e8d1;
    border-radius: 16px;
    padding: 22px 24px;
    margin-bottom: 22px;
    animation: fadeUp .45s .28s ease both;
}

.quiz-list { display: flex; flex-direction: column; gap: 10px }

.quiz-item {
    display: flex; align-items: center; gap: 14px;
    padding: 16px 18px;
    border-radius: 11px;
    border: 1.5px solid #e2f5ea;
    background: #f8fffe;
    text-decoration: none;
    transition: all .2s;
    position: relative;
    overflow: hidden;
}

.quiz-item::before {
    content: '';
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 3.5px;
    background: linear-gradient(180deg,#22c55e,#0d9488);
    border-radius: 3px;
}

.quiz-item:hover {
    border-color: #22c55e;
    background: #ecfdf5;
    box-shadow: 0 4px 18px rgba(21,128,61,.12);
    transform: translateX(4px);
}

.quiz-icon {
    width: 46px; height: 46px; border-radius: 11px;
    background: linear-gradient(135deg,#dcfce7,#bbf7d0);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; flex-shrink: 0;
}

.quiz-info { flex: 1 }

.quiz-name {
    font-size: .9rem; font-weight: 700; color: #0d1f14;
    margin-bottom: 5px;
}

.quiz-meta-row {
    display: flex; gap: 6px; flex-wrap: wrap;
}

.quiz-tag {
    font-size: .65rem; font-weight: 700;
    padding: 2px 9px; border-radius: 6px;
    background: #dcfce7; color: #166534;
}

.quiz-cta {
    display: flex; align-items: center; gap: 5px;
    background: #16a34a; color: #fff;
    font-size: .75rem; font-weight: 800;
    padding: 8px 16px; border-radius: 9px; flex-shrink: 0;
    transition: all .18s; white-space: nowrap;
    box-shadow: 0 2px 8px rgba(22,163,74,.25);
}
.quiz-item:hover .quiz-cta {
    background: #15803d;
    box-shadow: 0 4px 12px rgba(22,163,74,.35);
}

.btn-sub {
    display: flex; align-items: center; gap: 5px;
    font-size: .75rem; font-weight: 700;
    padding: 7px 14px; border-radius: 9px; text-decoration: none;
    border: 1.5px solid #c4e8d1; background: #fff; color: #15803d;
    transition: all .18s; white-space: nowrap;
}
.btn-sub:hover {
    background: #dcfce7; border-color: #22c55e; color: #166534;
}
.btn-sub.primary {
    background: #16a34a; color: #fff; border-color: #16a34a;
    box-shadow: 0 2px 8px rgba(22,163,74,.25);
}
.btn-sub.primary:hover {
    background: #15803d; border-color: #15803d;
}

.empty-state-new {
    text-align: center;
    padding: 36px 20px;
    color: #3d6b4f;
    font-size: .875rem;
    font-weight: 500;
    border: 1.5px dashed #c4e8d1;
    border-radius: 11px;
    background: #f8fffe;
}

@media (max-width: 960px) {
    .info-row { grid-template-columns: 1fr 1fr }
    .hero-emoji { display: none }
}
@media (max-width: 600px) {
    .hero { padding: 24px 22px }
    .hero-title { font-size: 1.4rem }
    .info-row { grid-template-columns: 1fr }
}
</style>
@endpush

@section('content')

    {{-- ── HERO ── --}}
    <div class="hero">
        <div class="hero-title">{{ $material->title }}</div>
        <div class="hero-desc">{{ \Illuminate\Support\Str::limit(strip_tags($material->description), 160) }}</div>
        <div class="hero-meta">
            @if ($material->topic)
                <div class="hero-meta-item">⭐ {{ $material->topic }}</div>
            @endif
            @if ($material->duration)
                <div class="hero-meta-item">⏱ {{ $material->duration }}</div>
            @endif
        </div>
        <div class="hero-emoji">📖</div>
    </div>

    {{-- ── INFO CARDS ── --}}
    <div class="info-row">
        <div class="info-card">
            <div class="info-card-icon">👨‍🏫</div>
            <div class="info-card-body">
                <div class="info-card-label">Pembuat Materi</div>
                <div class="info-card-val">{{ $material->creator?->name ?? 'Tidak diketahui' }}</div>
                <div class="info-card-sub">{{ $subject->name }}</div>
            </div>
        </div>

        <div class="info-card">
            <div class="info-card-icon">🏫</div>
            <div class="info-card-body">
                <div class="info-card-label">Mata Pelajaran</div>
                <div class="info-card-val">{{ $subject->name }}</div>
                <div class="info-card-sub">{{ $subject->kelasLabel() }}</div>
            </div>
        </div>

        <div class="info-card">
            <div class="info-card-icon">🗓️</div>
            <div class="info-card-body">
                <div class="info-card-label">Terakhir Diperbarui</div>
                <div class="info-card-val">{{ $material->updated_at?->format('d M Y') }}</div>
                <div class="info-card-sub">{{ $material->updated_at?->format('H:i') }} WIB</div>
            </div>
        </div>
    </div>

    {{-- ── DESKRIPSI ── --}}
    <div class="desc-section">
        <div class="sec-head">
            <div class="sec-head-line"></div>
            <div class="sec-title">📄 Isi Materi</div>
        </div>
        <div class="prose">{!! $material->description !!}</div>
    </div>

    {{-- ── FILE PDF ── --}}
    <div class="pdf-section">
        <div class="sec-head">
            <div class="sec-head-line"></div>
            <div class="sec-title">📎 File Pendukung</div>
        </div>

        @if ($material->file_path)
            <div class="pdf-list">
                <a href="{{ asset('storage/'.$material->file_path) }}" class="pdf-item" target="_blank" rel="noopener">
                    <div class="pdf-icon">📕</div>
                    <div class="pdf-info">
                        <div class="pdf-name">{{ $material->file_name ?? 'File Materi' }}</div>
                        <div class="pdf-meta">PDF · Klik untuk membuka</div>
                    </div>
                    <div class="pdf-action">🔗 Buka</div>
                </a>
            </div>
        @else
            <div class="empty-state-new">Belum ada file PDF untuk materi ini.</div>
        @endif
    </div>

    {{-- ── QUIZ ── --}}
    <div class="quiz-section">
        <div class="sec-head" style="justify-content: space-between;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="sec-head-line"></div>
                <div class="sec-title">🧠 Kuis &amp; Latihan Soal</div>
            </div>
            @if ($role === 'guru')
                <a class="btn-sub primary" href="{{ route('guru.materials.quizzes.create', [$subject, $material]) }}">
                    + Buat Kuis
                </a>
            @endif
        </div>

        @if ($quizzes->isEmpty())
            <div class="empty-state-new">Belum ada kuis pada materi ini.</div>
        @else
            <div class="quiz-list">
                @foreach ($quizzes as $quiz)
                    <div class="quiz-item">
                        <div class="quiz-icon">📝</div>
                        <div class="quiz-info">
                            <div class="quiz-name">{{ $quiz->title }}</div>
                            <div class="quiz-meta-row">
                                <span class="quiz-tag">{{ $quiz->questions_count }} Soal</span>
                                @if ($quiz->description)
                                    <span class="quiz-tag">{{ \Illuminate\Support\Str::limit($quiz->description, 40) }}</span>
                                @endif
                            </div>
                        </div>
                        <a class="quiz-cta" href="{{ route('quizzes.show', [$subject, $material, $quiz]) }}">
                            {{ $role === 'guru' ? 'Lihat Kuis' : 'Kerjakan' }} →
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

@endsection