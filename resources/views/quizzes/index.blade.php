@extends('layouts.portal')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,400&display=swap" rel="stylesheet"/>
<style>
/* ============================================================
   DESIGN TOKENS
   ============================================================ */
:root {
    --g100:#bbf7d0;--g200:#86efac;--g400:#22c55e;--g500:#16a34a;
    --g600:#15803d;--g700:#166534;--t400:#0d9488;--t600:#0f766e;
    --ink:#0d1f14;--ink2:#1e3a28;--ink3:#3d6b4f;
    --sf:#ffffff;--sf2:#f0faf4;--bd:#86efac;--bd2:#4ade80;
    --sh:0 2px 20px rgba(21,128,61,.18);--sh-lg:0 8px 48px rgba(21,128,61,.22);
    --r:14px;--rsm:9px;
}

@keyframes fadeUp {
    from{opacity:0;transform:translateY(18px)}
    to{opacity:1;transform:translateY(0)}
}

/* ============================================================
   TOPIC / QUIZ GRID
   ============================================================ */
.qx-grid {
    display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:28px;
}

.qx-card {
    background:var(--sf);border:2px solid var(--bd);border-radius:var(--r);
    padding:20px 18px;cursor:pointer;transition:all .25s;
    display:flex;flex-direction:column;gap:10px;
    text-decoration:none;color:inherit;
    animation:fadeUp .4s ease both;
}
.qx-card:nth-child(1){animation-delay:.05s}
.qx-card:nth-child(2){animation-delay:.10s}
.qx-card:nth-child(3){animation-delay:.15s}
.qx-card:nth-child(4){animation-delay:.20s}
.qx-card:nth-child(5){animation-delay:.25s}
.qx-card:nth-child(6){animation-delay:.30s}

.qx-card:hover {
    transform:translateY(-4px);box-shadow:var(--sh);border-color:var(--g400);
}

.qx-card-icon {
    width:44px;height:44px;border-radius:12px;
    background:var(--g100);
    display:flex;align-items:center;justify-content:center;font-size:1.3rem;
    flex-shrink:0;
}

.qx-card-subject {
    display:inline-flex;align-items:center;
    padding:3px 10px;border-radius:20px;
    background:var(--g100);border:1.5px solid var(--g400);
    font-size:.68rem;font-weight:700;color:var(--g700);
    width:fit-content;
}

.qx-card-name {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:.95rem;font-weight:800;color:var(--ink);line-height:1.3;
}

.qx-card-material {
    font-size:.78rem;color:var(--ink3);
}

.qx-card-stats {
    display:flex;gap:6px;flex-wrap:wrap;margin-top:2px;
}

.qx-stat {
    font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:8px;
    background:var(--g100);color:var(--g700);
}

.qx-stat-score {
    background:#dcfce7;color:var(--g600);
}

.qx-stat-new {
    background:#fef9c3;color:#854d0e;
}

.qx-stat-hard {
    background:#fee2e2;color:#991b1b;
}

.qx-stat-medium {
    background:#fef3c7;color:#92400e;
}

.qx-stat-easy {
    background:#d1fae5;color:#065f46;
}

/* GURU: extra info */
.qx-card-attempts {
    font-size:.75rem;color:var(--ink3);padding-top:4px;
    border-top:1px solid var(--bd);margin-top:4px;
}

/* ============================================================
   EMPTY STATE
   ============================================================ */
.qx-empty {
    background:var(--sf);border:2px dashed var(--bd2);border-radius:var(--r);
    padding:52px 28px;text-align:center;
    animation:fadeUp .4s ease both;
}
.qx-empty-icon {font-size:2.8rem;margin-bottom:12px}
.qx-empty-title {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:1.1rem;font-weight:800;margin-bottom:6px;color:var(--ink);
}
.qx-empty-sub {font-size:.875rem;color:var(--ink3);margin-bottom:20px}
.qx-btn-soft {
    display:inline-flex;align-items:center;gap:7px;
    padding:10px 22px;border-radius:var(--rsm);
    border:2px solid var(--bd);background:var(--sf2);
    font-family:'DM Sans',sans-serif;font-weight:700;font-size:.875rem;
    color:var(--ink3);text-decoration:none;transition:all .2s;
}
.qx-btn-soft:hover{background:var(--g100);border-color:var(--g400);color:var(--ink)}

/* ============================================================
   CREATE BUTTON — guru
   ============================================================ */
.qx-btn-create {
    display:inline-flex;align-items:center;gap:7px;
    padding:9px 18px;border-radius:var(--rsm);
    border:none;background:var(--g500);
    font-family:'DM Sans',sans-serif;font-weight:700;font-size:.875rem;
    color:#fff;text-decoration:none;cursor:pointer;
    transition:background .2s, transform .15s, box-shadow .2s;
    box-shadow:0 2px 8px rgba(21,128,61,.25);
}
.qx-btn-create:hover {
    background:var(--g600);transform:translateY(-1px);
    box-shadow:0 4px 14px rgba(21,128,61,.32);color:#fff;
}
.qx-btn-create:active{transform:translateY(0)}

/* ============================================================
   RECENT SCORES (siswa only)
   ============================================================ */
.qx-recent-title {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:1rem;font-weight:800;margin-bottom:12px;color:var(--ink);
    display:flex;align-items:center;gap:8px;
}
.qx-recent-list {display:flex;flex-direction:column;gap:8px}

.qx-recent-item {
    background:var(--sf);border:2px solid var(--bd);border-radius:var(--rsm);
    padding:12px 16px;display:flex;align-items:center;gap:14px;
    text-decoration:none;color:inherit;transition:all .2s;
    animation:fadeUp .4s ease both;
}
.qx-recent-item:hover{border-color:var(--g400);background:var(--sf2)}

.qx-ri-icon {
    width:38px;height:38px;border-radius:10px;
    background:var(--g100);display:flex;align-items:center;
    justify-content:center;font-size:1.1rem;flex-shrink:0;
}
.qx-ri-name {font-size:.875rem;font-weight:700;flex:1;color:var(--ink)}
.qx-ri-meta {font-size:.75rem;color:var(--ink3);margin-top:1px}
.qx-ri-score {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:1rem;font-weight:800;color:var(--g600);
}

/* ============================================================
   SECTION DIVIDER
   ============================================================ */
.qx-section-header {
    display:flex;align-items:center;justify-content:space-between;
    margin-bottom:14px;
}
.qx-section-title {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:1rem;font-weight:800;color:var(--ink);
    display:flex;align-items:center;gap:8px;
}
.qx-count-badge {
    display:inline-flex;align-items:center;
    padding:3px 10px;border-radius:20px;
    background:var(--g100);border:1.5px solid var(--g400);
    font-size:.68rem;font-weight:700;color:var(--g700);
}

@media(max-width:960px) {
    .qx-grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:540px) {
    .qx-grid{grid-template-columns:1fr}
}
</style>
@endpush

@section('heading', 'Kuis')
@section('subtitle', 'Pilih kuis untuk mulai mengerjakan latihan soal atau lihat hasil pengerjaan sebelumnya.')

@section('content')
<div style="font-family:'DM Sans',sans-serif;color:var(--ink)">

    {{-- ============================================================
         QUIZ GRID
         ============================================================ --}}
    @if ($quizzes->isEmpty())
        <div class="qx-empty">
            <div class="qx-empty-icon">📋</div>
            <div class="qx-empty-title">Belum ada kuis tersedia</div>
            <div class="qx-empty-sub">
                {{ $role === 'guru'
                    ? 'Buat kuis pertama kamu dari halaman materi.'
                    : 'Kuis akan muncul di sini setelah guru membuat latihan soal.' }}
            </div>
            <a href="{{ $emptyActionRoute }}" class="qx-btn-soft">
                {{ $emptyActionLabel }}
            </a>
        </div>
    @else
        <div class="qx-section-header">
            <div class="qx-section-title">
                📚 Semua Kuis
                <span class="qx-count-badge">{{ $quizzes->count() }} kuis</span>
            </div>

            @if ($role === 'guru')
                <a href="{{ route('guru.materials') }}" class="qx-btn-create"
                   title="Pilih materi terlebih dahulu, lalu buat kuis dari halaman materi">
                    ＋ Buat Kuis
                </a>
            @endif
        </div>

        <div class="qx-grid">
            @foreach ($quizzes as $quiz)
                @php
                    $icons = ['✍️','📖','📝','🧠','🔬','🌍','🎯','💡'];
                    $icon  = $icons[$loop->index % count($icons)];

                    $diffClass = match($quiz->difficulty ?? 'sedang') {
                        'mudah' => 'qx-stat-easy',
                        'susah' => 'qx-stat-hard',
                        default => 'qx-stat-medium',
                    };
                    $diffLabel = match($quiz->difficulty ?? 'sedang') {
                        'mudah' => 'Mudah',
                        'susah' => 'Susah',
                        default => 'Sedang',
                    };
                @endphp
                <a class="qx-card"
                   href="{{ route('quizzes.show', [$quiz->material->subject, $quiz->material, $quiz]) }}">

                    <div class="qx-card-icon">{{ $icon }}</div>

                    <span class="qx-card-subject">
                        {{ $quiz->material->subject->name }}
                    </span>

                    <div class="qx-card-name">{{ $quiz->title }}</div>

                    <div class="qx-card-material">{{ $quiz->material->title }}</div>

                    <div class="qx-card-stats">
                        <span class="qx-stat">{{ $quiz->questions_count }} soal</span>
                        <span class="qx-stat">{{ $quiz->duration }} menit</span>
                        <span class="qx-stat {{ $diffClass }}">{{ $diffLabel }}</span>

                        @if ($role === 'siswa')
                            @if ($quiz->latest_attempt)
                                <span class="qx-stat qx-stat-score">
                                    {{ $quiz->latest_attempt->score }}%
                                </span>
                            @else
                                <span class="qx-stat qx-stat-new">Baru</span>
                            @endif
                        @endif
                    </div>

                    @if ($role === 'guru')
                        <div class="qx-card-attempts">
                            👥 {{ $quiz->attempts_count }} attempt siswa
                        </div>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- ============================================================
             RECENT SCORES — siswa only
             ============================================================ --}}
        @if ($role === 'siswa')
            @php
                $attempted = $quizzes->filter(fn($q) => $q->latest_attempt !== null);
            @endphp

            @if ($attempted->isNotEmpty())
                <div class="qx-recent-title">📊 Riwayat Pengerjaan</div>
                <div class="qx-recent-list">
                    @foreach ($attempted->sortByDesc(fn($q) => $q->latest_attempt->submitted_at)->take(5) as $quiz)
                        <a class="qx-recent-item"
                           href="{{ route('quizzes.show', [$quiz->material->subject, $quiz->material, $quiz]) }}"
                           style="animation-delay:{{ $loop->index * 0.08 }}s">
                            <div class="qx-ri-icon">✍️</div>
                            <div>
                                <div class="qx-ri-name">{{ $quiz->title }}</div>
                                <div class="qx-ri-meta">
                                    {{ $quiz->material->subject->name }} ·
                                    {{ $quiz->latest_attempt->submitted_at?->diffForHumans() ?? '-' }}
                                </div>
                            </div>
                            <div class="qx-ri-score">{{ $quiz->latest_attempt->score }}%</div>
                        </a>
                    @endforeach
                </div>
            @endif
        @endif
    @endif

</div>
@endsection