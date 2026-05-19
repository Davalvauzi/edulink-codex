@extends('layouts.portal')

@section('heading', $subject->name)
@section('subtitle', 'Halaman ini menampilkan daftar materi. Klik salah satu untuk masuk ke detail materi.')

@section('actions')
    @if ($role === 'guru')
        <a class="btn btn-primary" href="{{ route('guru.subjects.materials.create', $subject) }}">+ Tambah Materi</a>
    @endif
@endsection

@push('styles')
<style>
@keyframes materi-fadeUp {
    from { opacity: 0; transform: translateY(18px) }
    to   { opacity: 1; transform: translateY(0) }
}
@keyframes materi-pulseDot {
    0%,100% { opacity: 1; transform: scale(1) }
    50%      { opacity: .4; transform: scale(.75) }
}
@keyframes materi-fillBar {
    from { width: 0 }
    to   { width: var(--materi-target-w) }
}
@keyframes materi-countUp {
    from { opacity: 0; transform: translateY(6px) }
    to   { opacity: 1; transform: translateY(0) }
}

/* ── INFO CARDS ── */
.materi-info-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 24px;
    animation: materi-fadeUp .4s ease both;
}

.materi-info-card {
    background: #fff;
    border: 2px solid #86efac;
    border-radius: 14px;
    padding: 16px 18px;
}

.materi-info-card-label {
    font-size: .65rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: 1px;
    color: #3d6b4f; margin-bottom: 4px;
}

.materi-info-card-val {
    font-size: 1rem; font-weight: 800; color: #0d1f14;
}

/* ── PROGRESS SECTION ── */
.materi-progress-section {
    background: #fff;
    border: 2px solid #86efac;
    border-radius: 14px;
    padding: 22px 24px;
    margin-bottom: 28px;
    animation: materi-fadeUp .45s ease both;
    position: relative;
    overflow: hidden;
}

.materi-progress-section::after {
    content: '';
    position: absolute; right: -40px; top: -40px;
    width: 180px; height: 180px; border-radius: 50%;
    background: radial-gradient(circle, #d1fae5 0%, transparent 70%);
    pointer-events: none;
}

.materi-progress-top {
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: 12px;
    margin-bottom: 20px;
}

.materi-progress-label-group { display: flex; flex-direction: column; gap: 2px }

.materi-progress-eyebrow {
    font-size: .65rem; font-weight: 800;
    letter-spacing: 1.2px; text-transform: uppercase;
    color: #15803d;
}

.materi-progress-heading {
    font-size: 1.05rem; font-weight: 800; color: #0d1f14;
}

.materi-progress-badge {
    display: flex; align-items: center; gap: 6px;
    background: #f0fdf4; border: 2px solid #86efac;
    border-radius: 30px; padding: 5px 12px;
    font-size: .75rem; font-weight: 800; color: #15803d;
    white-space: nowrap; flex-shrink: 0;
}

.materi-progress-badge-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #22c55e;
    animation: materi-pulseDot 1.8s ease-in-out infinite;
}

.materi-progress-bar-wrap { margin-bottom: 20px }

.materi-progress-bar-info {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 8px;
}

.materi-progress-bar-label { font-size: .8rem; font-weight: 600; color: #3d6b4f }

.materi-progress-bar-pct {
    font-size: 1.35rem; font-weight: 800; color: #15803d;
    animation: materi-countUp .7s .2s ease both;
}

.materi-progress-track {
    height: 14px; background: #e9f7ef; border-radius: 99px;
    overflow: hidden; border: 1.5px solid #86efac;
}

.materi-progress-fill {
    height: 100%; border-radius: 99px;
    background: linear-gradient(90deg, #22c55e, #0d9488);
    width: 0;
    animation: materi-fillBar .9s .3s cubic-bezier(.4,0,.2,1) forwards;
    position: relative;
}

.materi-progress-fill::after {
    content: ''; position: absolute;
    right: 0; top: 0; bottom: 0; width: 28px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.35));
}

.materi-progress-stats {
    display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;
}

.materi-progress-stat {
    background: #f6fef9; border: 1.5px solid #bbf7d0;
    border-radius: 10px; padding: 11px 14px;
    display: flex; flex-direction: column; gap: 2px;
    transition: border-color .2s, box-shadow .2s;
}

.materi-progress-stat:hover {
    border-color: #86efac;
    box-shadow: 0 2px 12px rgba(21,128,61,.1);
}

.materi-progress-stat-icon { font-size: 1.1rem; margin-bottom: 2px }

.materi-progress-stat-val {
    font-size: 1.1rem; font-weight: 800; color: #0d1f14;
    animation: materi-countUp .6s ease both;
}

.materi-progress-stat-lbl {
    font-size: .67rem; font-weight: 600; color: #3d6b4f;
    text-transform: uppercase; letter-spacing: .8px;
}

/* ── SECTION HEAD ── */
.materi-sec-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px;
}

.materi-sec-title {
    font-size: 1.05rem; font-weight: 800; color: #1e3a28;
}

/* ── GRID ── */
.materi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

/* ── CARD ── */
.materi-card {
    background: #fff;
    border: 2px solid #86efac;
    border-radius: 14px;
    overflow: hidden;
    transition: all .28s;
    animation: materi-fadeUp .5s ease both;
    position: relative;
    display: flex; flex-direction: column;
    text-decoration: none; color: inherit;
}

.materi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 48px rgba(21,128,61,.22);
}

.materi-card-thumb {
    height: 110px;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.4rem;
    background: #fff;
    border-bottom: 2px solid #86efac;
    position: relative; overflow: hidden;
}

.materi-card-thumb img {
    width: 100%; height: 100%; object-fit: cover; display: block;
}

.materi-card-actions {
    position: absolute; top: 8px; right: 8px;
    display: flex; gap: 6px;
    opacity: 0; transition: opacity .2s;
}

.materi-card:hover .materi-card-actions { opacity: 1 }

.materi-card-action-btn {
    width: 30px; height: 30px; border-radius: 8px;
    border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem; transition: transform .15s;
}

.materi-card-action-btn:hover { transform: scale(1.1) }

.materi-card-action-edit {
    background: rgba(255,255,255,.92); color: #15803d;
}

.materi-card-action-del {
    background: rgba(254,242,242,.95); color: #dc2626;
}

.materi-card-body {
    padding: 14px 16px 16px; flex: 1; display: flex; flex-direction: column;
}

.materi-card-cat {
    font-size: .65rem; font-weight: 800;
    letter-spacing: 1px; text-transform: uppercase;
    color: #15803d; margin-bottom: 4px;
}

.materi-card-name {
    font-size: .95rem; font-weight: 800; margin-bottom: 6px; color: #0d1f14;
}

.materi-card-desc {
    font-size: .78rem; color: #3d6b4f;
    margin-bottom: 12px; flex: 1;
    overflow: hidden; display: -webkit-box;
    -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    word-break: break-word; line-height: 1.5;
    min-height: calc(1.5em * 2);
}

.materi-card-meta {
    display: flex; gap: 12px; flex-wrap: nowrap; margin-bottom: 12px;
}

.materi-meta-item {
    font-size: .7rem; color: #3d6b4f;
    display: flex; align-items: center; gap: 3px;
}

.materi-card-btn {
    display: inline-block;
    font-size: .75rem; font-weight: 800;
    padding: 6px 14px; border-radius: 8px;
    background: #16a34a; color: #fff;
    border: none; cursor: pointer;
    text-decoration: none; transition: opacity .18s;
    align-self: flex-start;
}

.materi-card-btn:hover { opacity: .85 }

.materi-del-form { margin: 0; display: inline }

/* ── EMPTY STATE ── */
.materi-empty {
    text-align: center; padding: 60px 20px;
    background: #fff; border: 2px dashed #86efac;
    border-radius: 14px; color: #3d6b4f;
}

.materi-empty-icon { font-size: 2.5rem; margin-bottom: 12px }

.materi-empty-title {
    font-size: 1rem; font-weight: 800;
    margin-bottom: 6px; color: #0d1f14;
}

.materi-empty-sub { font-size: .82rem }

/* ── RESPONSIVE ── */
@media (max-width: 960px) {
    .materi-grid { grid-template-columns: repeat(2, 1fr) }
    .materi-info-row { grid-template-columns: 1fr 1fr }
}

@media (max-width: 600px) {
    .materi-grid { grid-template-columns: 1fr }
    .materi-info-row { grid-template-columns: 1fr }
    .materi-progress-top { flex-direction: column; gap: 8px }
}
</style>
@endpush

@section('content')

    {{-- ── INFO CARDS (guru only) ── --}}
    @if ($role === 'guru')
        <div class="materi-info-row">
            <div class="materi-info-card">
                <div class="materi-info-card-label">Kelas</div>
                <div class="materi-info-card-val">{{ $subject->kelasLabel() }}</div>
            </div>
            <div class="materi-info-card">
                <div class="materi-info-card-label">Pembuat</div>
                <div class="materi-info-card-val">{{ $subject->creator?->name ?? 'Tidak diketahui' }}</div>
            </div>
            <div class="materi-info-card">
                <div class="materi-info-card-label">Total Materi</div>
                <div class="materi-info-card-val">{{ $subject->materials->count() }} materi</div>
            </div>
        </div>
    @endif

    {{-- ── PROGRESS (siswa only) ── --}}
    @if (false && $role === 'siswa' && $subject->materials->count() > 0)
        @php
            $totalMat     = $subject->materials->count();
            $completedMat = $subject->materials->filter(function ($m) use ($user) {
                if (! $m->relationLoaded('subsections') || $m->subsections->isEmpty()) return false;
                return $m->subsections->every(
                    fn($s) => $s->relationLoaded('progressRecords') &&
                              $s->progressRecords->where('user_id', $user->id)->isNotEmpty()
                );
            })->count();
            $pct = $totalMat > 0 ? (int) round(($completedMat / $totalMat) * 100) : 0;
        @endphp

        <div class="materi-progress-section">
            <div class="materi-progress-top">
                <div class="materi-progress-label-group">
                    <span class="materi-progress-eyebrow">Progres Kamu</span>
                    <span class="materi-progress-heading">Progress Belajar {{ $subject->name }}</span>
                </div>
                <div class="materi-progress-badge">
                    <span class="materi-progress-badge-dot"></span>
                    Sedang Berjalan
                </div>
            </div>

            <div class="materi-progress-bar-wrap">
                <div class="materi-progress-bar-info">
                    <span class="materi-progress-bar-label">Total keseluruhan materi diselesaikan</span>
                    <span class="materi-progress-bar-pct">{{ $pct }}%</span>
                </div>
                <div class="materi-progress-track">
                    <div class="materi-progress-fill" style="--materi-target-w: {{ $pct }}%"></div>
                </div>
            </div>

            <div class="materi-progress-stats">
                <div class="materi-progress-stat">
                    <div class="materi-progress-stat-icon">✅</div>
                    <div class="materi-progress-stat-val">{{ $completedMat }}</div>
                    <div class="materi-progress-stat-lbl">Materi Selesai</div>
                </div>
                <div class="materi-progress-stat">
                    <div class="materi-progress-stat-icon">📖</div>
                    <div class="materi-progress-stat-val">{{ $totalMat - $completedMat }}</div>
                    <div class="materi-progress-stat-lbl">Materi Belum Selesai</div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── DAFTAR MATERI ── --}}
    <div class="materi-sec-head">
        <div class="materi-sec-title">📚 Semua Materi</div>
    </div>

    @if ($subject->materials->isEmpty())
        <div class="materi-empty">
            <div class="materi-empty-icon">📭</div>
            <div class="materi-empty-title">Belum ada materi</div>
            <div class="materi-empty-sub">
                @if ($role === 'guru')
                    Klik "+ Tambah Materi" di atas untuk menambahkan materi pertama.
                @else
                    Guru belum menambahkan materi pada mata pelajaran ini.
                @endif
            </div>
        </div>
    @else
        <div class="materi-grid">
            @foreach ($subject->materials as $index => $material)
                <div class="materi-card" style="animation-delay: {{ $index * 0.06 }}s">

                    <div class="materi-card-thumb">
                        @if ($material->thumbnail_url)
                            <img src="{{ $material->thumbnail_url }}" alt="{{ $material->title }}"/>
                        @else
                            <span>📄</span>
                        @endif

                        @if ($role === 'guru')
                            <div class="materi-card-actions">
                                <a href="{{ route('guru.materials.edit', [$subject, $material]) }}"
                                   class="materi-card-action-btn materi-card-action-edit"
                                   title="Edit">✏️</a>
                                <form class="materi-del-form" method="POST"
                                      action="{{ route('guru.materials.destroy', [$subject, $material]) }}"
                                      onsubmit="return confirm('Hapus materi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="materi-card-action-btn materi-card-action-del"
                                            title="Hapus">🗑️</button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <div class="materi-card-body">
                        @if ($material->topic)
                            <div class="materi-card-cat">{{ $material->topic }}</div>
                        @endif

                        <div class="materi-card-name">{{ $material->title }}</div>

                        <div class="materi-card-desc">
                            {{ \Illuminate\Support\Str::limit(strip_tags($material->description), 120) }}
                        </div>

                        <div class="materi-card-meta">
                            <span class="materi-meta-item">📘 {{ !empty($material->file_path) ? 1 : 0 }} modul</span>
                            <span class="materi-meta-item">⏱ {{ $material->duration ?? '-' }}</span>
                            <span class="materi-meta-item">👤 {{ $material->creator?->name ?? '-' }}</span>
                        </div>

                        <a class="materi-card-btn" href="{{ route('materials.show', [$subject, $material]) }}">
                            {{ $role === 'guru' ? 'Lihat Detail →' : 'Mulai Belajar →' }}
                        </a>
                    </div>

                </div>
            @endforeach
        </div>
    @endif

@endsection
