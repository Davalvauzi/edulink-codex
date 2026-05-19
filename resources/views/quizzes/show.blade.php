@extends('layouts.portal')

@php
    $wrongAnswers  = $latestAttempt?->answers->where('is_correct', false) ?? collect();
    $resultAnswers = $latestAttempt?->answers->sortBy(function($a) { return $a->question->position; }) ?? collect();

    $diffClass = match($quiz->difficulty ?? 'sedang') {
        'mudah' => 'qs-pill-easy',
        'susah' => 'qs-pill-hard',
        default => 'qs-pill-medium',
    };
    $diffLabel = match($quiz->difficulty ?? 'sedang') {
        'mudah' => 'Mudah',
        'susah' => 'Susah',
        default => 'Sedang',
    };

    $questionsJson = $quiz->questions->map(function($q) {
        return [
            'id'             => $q->id,
            'position'       => $q->position,
            'question'       => $q->question,
            'options'        => $q->options,
            'correct_option' => $q->correct_option,
            'explanation'    => $q->explanation ?? '',
            'image_source'   => $q->image_source ?? null,
        ];
    })->values();
@endphp

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,400&display=swap" rel="stylesheet"/>
<style>
:root {
    --g100:#bbf7d0;--g200:#86efac;--g400:#22c55e;--g500:#16a34a;
    --g600:#15803d;--g700:#166534;--t400:#0d9488;--t600:#0f766e;
    --ink:#0d1f14;--ink2:#1e3a28;--ink3:#3d6b4f;
    --sf:#ffffff;--sf2:#f0faf4;--bd:#86efac;--bd2:#4ade80;
    --sh:0 2px 20px rgba(21,128,61,.18);--sh-lg:0 8px 48px rgba(21,128,61,.22);
    --r:16px;--rsm:10px;
}

@keyframes fadeUp {
    from{opacity:0;transform:translateY(20px)}
    to{opacity:1;transform:translateY(0)}
}
@keyframes fadeIn {
    from{opacity:0}to{opacity:1}
}
@keyframes shake {
    0%,100%{transform:translateX(0)}
    20%,60%{transform:translateX(-7px)}
    40%,80%{transform:translateX(7px)}
}
@keyframes correctPop {
    0%{transform:scale(1)}
    40%{transform:scale(1.04)}
    100%{transform:scale(1)}
}
@keyframes resultDrop {
    0%{opacity:0;transform:translateY(-30px) scale(.95)}
    60%{transform:translateY(4px) scale(1.02)}
    100%{opacity:1;transform:translateY(0) scale(1)}
}
@keyframes scoreCount {
    0%{transform:scale(1.3);color:var(--g400)}
    100%{transform:scale(1);color:var(--g600)}
}
@keyframes confettiFall {
    0%{transform:translateY(-10px) rotate(0deg);opacity:1}
    100%{transform:translateY(80px) rotate(360deg);opacity:0}
}

/* ============================================================
   WRAP
   ============================================================ */
.qs-wrap {
    font-family:'DM Sans',sans-serif;
    color:var(--ink);
    max-width:760px;
    margin:0 auto;
}

/* ============================================================
   SESSION HEADER
   ============================================================ */
.qs-session-header {
    display:flex;align-items:flex-start;justify-content:space-between;
    gap:14px;margin-bottom:20px;
    animation:fadeUp .4s ease both;
}
.qs-session-topic {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:1.3rem;font-weight:800;line-height:1.3;
}
.qs-session-meta {
    font-size:.8rem;color:var(--ink3);margin-top:3px;
}
.qs-pill-row {display:flex;gap:8px;flex-wrap:wrap;flex-shrink:0}
.qs-pill {
    font-size:.72rem;font-weight:700;padding:4px 12px;border-radius:10px;
    border:1.5px solid var(--bd);background:var(--sf);color:var(--ink3);
    white-space:nowrap;
}
.qs-pill-easy   {border-color:var(--g400);background:var(--g100);color:var(--g700)}
.qs-pill-medium {border-color:#f59e0b;background:#fef3c7;color:#92400e}
.qs-pill-hard   {border-color:#ef4444;background:#fee2e2;color:#991b1b}

/* ============================================================
   INFO CARDS
   ============================================================ */
.qs-info-row {
    display:grid;grid-template-columns:repeat(3,1fr);gap:12px;
    margin-bottom:22px;animation:fadeUp .4s ease .05s both;
}
.qs-info-card {
    background:var(--sf);border:2px solid var(--bd);border-radius:var(--rsm);
    padding:14px 16px;
}
.qs-info-label {font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--ink3);margin-bottom:4px}
.qs-info-val   {font-size:.9rem;font-weight:700;color:var(--ink)}

/* ============================================================
   DESCRIPTION
   ============================================================ */
.qs-desc {
    background:var(--sf);border:2px solid var(--bd);border-radius:var(--rsm);
    padding:16px 18px;margin-bottom:20px;
    animation:fadeUp .4s ease .08s both;
}
.qs-desc-label {font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--ink3);margin-bottom:5px}
.qs-desc-text  {font-size:.875rem;color:var(--ink2)}

/* ============================================================
   SCORE BANNER (latest attempt)
   ============================================================ */
.qs-score-banner {
    background:var(--sf);border:2px solid var(--g400);border-radius:var(--r);
    padding:24px 28px;margin-bottom:22px;
    display:flex;align-items:center;gap:20px;
    animation:fadeUp .4s ease .1s both;
    position:relative;overflow:hidden;
}
.qs-score-banner::before {
    content:'';position:absolute;inset:0;
    background:radial-gradient(ellipse 60% 80% at 0% 50%, rgba(22,163,74,.07) 0%, transparent 70%);
    pointer-events:none;
}
.qs-score-circle {
    flex-shrink:0;width:74px;height:74px;border-radius:50%;
    background:linear-gradient(135deg,var(--g600),var(--t400));
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    box-shadow:var(--sh);
}
.qs-score-num {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:1.4rem;font-weight:800;color:#fff;line-height:1;
}
.qs-score-pct {font-size:.65rem;font-weight:700;color:rgba(255,255,255,.75)}
.qs-score-info {flex:1}
.qs-score-title {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:1rem;font-weight:800;margin-bottom:4px;
}
.qs-score-sub {font-size:.82rem;color:var(--ink3);margin-bottom:12px}
.qs-score-actions {display:flex;gap:8px;flex-wrap:wrap}

.qs-btn-soft {
    display:inline-flex;align-items:center;gap:6px;
    padding:8px 16px;border-radius:var(--rsm);
    border:2px solid var(--bd);background:var(--sf2);
    font-family:'DM Sans',sans-serif;font-weight:700;font-size:.8rem;
    color:var(--ink3);text-decoration:none;cursor:pointer;transition:all .2s;
}
.qs-btn-soft:hover{background:var(--g100);border-color:var(--g400);color:var(--ink)}

/* ============================================================
   PROGRESS (siswa mode)
   ============================================================ */
.qs-progress-wrap {
    margin-bottom:24px;
    animation:fadeUp .4s ease .12s both;
}
.qs-progress-top {
    display:flex;justify-content:space-between;
    margin-bottom:8px;font-size:.8rem;font-weight:700;
}
.qs-score-live {color:var(--g600)}
.qs-score-live.bump {animation:scoreCount .4s ease}
.qs-progress-track {
    height:10px;background:var(--g100);border-radius:10px;overflow:hidden;
}
.qs-progress-fill {
    height:100%;
    background:linear-gradient(90deg,var(--g500),var(--t400));
    border-radius:10px;transition:width .5s cubic-bezier(.4,0,.2,1);
}
.qs-step-dots {display:flex;gap:6px;margin-top:10px;flex-wrap:wrap}
.qs-dot {
    width:8px;height:8px;border-radius:50%;
    background:var(--g100);border:1.5px solid var(--bd);
    transition:all .3s;
}
.qs-dot.done-correct {background:var(--g500);border-color:var(--g500)}
.qs-dot.done-wrong   {background:#ef4444;border-color:#ef4444}
.qs-dot.done-skip    {background:#d1d5db;border-color:#d1d5db}
.qs-dot.current      {background:var(--t400);border-color:var(--t400);transform:scale(1.3)}

/* ============================================================
   QUESTION CARD
   ============================================================ */
.qs-q-card {
    background:var(--sf);border:2px solid var(--bd);border-radius:var(--r);
    padding:28px 28px 24px;margin-bottom:14px;
    animation:fadeUp .35s ease both;
}
.qs-q-top {
    display:flex;align-items:flex-start;gap:14px;margin-bottom:22px;
}
.qs-q-num-badge {
    flex-shrink:0;width:36px;height:36px;border-radius:10px;
    background:var(--g100);
    display:flex;align-items:center;justify-content:center;
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:.9rem;font-weight:800;color:var(--g700);
}
.qs-q-text {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:1.1rem;font-weight:700;line-height:1.5;padding-top:5px;
}
.qs-q-img {
    width:100%;max-height:220px;object-fit:contain;border-radius:var(--rsm);
    border:2px solid var(--bd);margin-bottom:16px;background:var(--sf2);
}

/* OPTIONS — quiz mode */
.qs-options {display:flex;flex-direction:column;gap:10px}
.qs-option {
    display:flex;align-items:center;gap:14px;
    padding:14px 18px;border-radius:var(--rsm);
    border:2px solid var(--bd);background:var(--sf2);
    cursor:pointer;transition:all .2s;
    font-size:.9rem;font-weight:600;text-align:left;width:100%;
    font-family:'DM Sans',sans-serif;
}
.qs-option:hover:not(:disabled){
    border-color:var(--g400);background:var(--g100);transform:translateX(4px);
}
.qs-option:disabled {cursor:not-allowed}
.qs-opt-letter {
    flex-shrink:0;width:30px;height:30px;border-radius:8px;
    display:flex;align-items:center;justify-content:center;
    background:var(--bd);color:var(--g700);
    font-weight:800;font-size:.85rem;transition:all .2s;
}
.qs-option.correct {background:#dcfce7;border-color:var(--g500);animation:correctPop .3s ease}
.qs-option.correct .qs-opt-letter {background:var(--g500);color:#fff}
.qs-option.wrong   {background:#fee2e2;border-color:#ef4444;animation:shake .4s ease}
.qs-option.wrong   .qs-opt-letter {background:#ef4444;color:#fff}

/* OPTIONS — review/guru mode */
.qs-choice-list {display:flex;flex-direction:column;gap:8px}
.qs-choice {
    display:flex;align-items:center;gap:12px;
    padding:12px 16px;border-radius:var(--rsm);
    border:2px solid var(--bd);background:var(--sf2);
    font-size:.875rem;font-weight:600;
}
.qs-choice.qs-choice-correct {
    border-color:var(--g500);background:#dcfce7;
}
.qs-choice.qs-choice-selected {
    border-color:#ef4444;background:#fee2e2;
}
.qs-choice.qs-choice-correct.qs-choice-selected {
    border-color:var(--g500);background:#dcfce7;
}

/* EXPLANATION */
.qs-explanation {
    margin-top:14px;border-radius:var(--rsm);
    border:2px solid #99f6e4;background:rgba(13,148,136,.07);
    padding:14px 16px;display:none;animation:fadeIn .3s ease;
}
.qs-explanation.visible {display:block}
.qs-exp-label {
    font-size:.7rem;font-weight:800;text-transform:uppercase;
    letter-spacing:.8px;color:var(--t600);margin-bottom:5px;
}
.qs-exp-text {font-size:.875rem;line-height:1.65;color:var(--ink2)}

/* ANSWER BADGE */
.qs-ans-badge {
    display:inline-flex;align-items:center;gap:5px;
    padding:4px 10px;border-radius:8px;font-size:.75rem;font-weight:800;
    flex-shrink:0;
}
.qs-ans-correct {background:#dcfce7;color:var(--g700)}
.qs-ans-wrong   {background:#fee2e2;color:#b91c1c}
.qs-ans-skip    {background:#f3f4f6;color:#6b7280}

/* KEY PILL (guru) */
.qs-key-pill {
    display:inline-flex;align-items:center;gap:5px;
    padding:4px 12px;border-radius:8px;
    font-size:.75rem;font-weight:800;
    background:var(--g100);color:var(--g700);border:1.5px solid var(--g400);
}

/* ============================================================
   ACTION ROW (siswa quiz mode)
   ============================================================ */
.qs-action-row {
    display:flex;justify-content:space-between;align-items:center;gap:12px;
    margin-bottom:32px;animation:fadeUp .35s ease .1s both;
}
.qs-action-left  {display:flex;gap:8px}
.qs-action-right {display:flex;gap:8px}
.qs-btn-prev {
    padding:12px 18px;border-radius:var(--rsm);
    border:2px solid var(--bd);background:var(--sf);
    font-weight:700;font-size:.88rem;cursor:pointer;
    transition:all .2s;color:var(--ink3);font-family:'DM Sans',sans-serif;
}
.qs-btn-prev:hover:not(:disabled){background:var(--g100);color:var(--ink)}
.qs-btn-prev:disabled{opacity:.35;cursor:not-allowed}
.qs-btn-skip {
    padding:12px 18px;border-radius:var(--rsm);
    border:2px solid var(--bd);background:var(--sf);
    font-weight:700;font-size:.88rem;cursor:pointer;
    transition:all .2s;color:var(--ink3);font-family:'DM Sans',sans-serif;
}
.qs-btn-skip:hover:not(:disabled){background:#fef3c7;border-color:#f59e0b;color:#92400e}
.qs-btn-skip:disabled{opacity:.35;cursor:not-allowed}
.qs-btn-next {
    padding:12px 24px;border-radius:var(--rsm);
    background:linear-gradient(135deg,var(--g600),var(--t400));
    border:none;color:#fff;font-weight:800;font-size:.92rem;
    cursor:pointer;transition:all .2s;
    display:flex;align-items:center;gap:8px;
    font-family:'DM Sans',sans-serif;
}
.qs-btn-next:hover{transform:translateY(-2px);box-shadow:var(--sh)}
.qs-btn-next.finish {background:linear-gradient(135deg,#0f766e,#16a34a)}
.qs-btn-submit {
    width:100%;padding:15px;border-radius:var(--r);
    background:linear-gradient(135deg,var(--g600),var(--t400));
    border:none;color:#fff;
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:1.05rem;font-weight:800;
    cursor:pointer;transition:all .25s;box-shadow:var(--sh);
    margin-top:10px;
}
.qs-btn-submit:hover{transform:translateY(-2px);box-shadow:var(--sh-lg)}

/* ============================================================
   RESULT HERO
   ============================================================ */
.qs-result-hero {
    background:var(--sf);border:2px solid var(--bd);
    border-radius:var(--r);padding:44px 28px;text-align:center;
    margin-bottom:22px;position:relative;overflow:hidden;
    animation:fadeUp .45s ease both;
}
.qs-result-hero::before {
    content:'';position:absolute;inset:0;
    background:radial-gradient(ellipse 60% 60% at 50% 0%, rgba(22,163,74,.08) 0%, transparent 70%);
    pointer-events:none;
}
.qs-result-emoji {font-size:3.5rem;display:block;margin-bottom:12px;animation:resultDrop .6s ease both}
.qs-result-title {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:1.8rem;font-weight:800;margin-bottom:6px;
}
.qs-result-sub {font-size:.875rem;color:var(--ink3);margin-bottom:24px}
.qs-score-big {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:3.5rem;font-weight:800;
    background:linear-gradient(135deg,var(--g600),var(--t400));
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;
    display:block;margin-bottom:4px;
}
.qs-score-label {font-size:.82rem;color:var(--ink3);margin-bottom:24px}
.qs-result-stats {
    display:flex;justify-content:center;gap:14px;margin-bottom:24px;flex-wrap:wrap;
}
.qs-rs-item {
    text-align:center;padding:12px 18px;
    border-radius:var(--rsm);border:2px solid var(--bd);
    background:var(--sf2);min-width:80px;
}
.qs-rs-val {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:1.4rem;font-weight:800;color:var(--g600);
}
.qs-rs-lbl {font-size:.7rem;color:var(--ink3);font-weight:700}
.qs-result-btn-row {display:flex;justify-content:center;gap:10px;flex-wrap:wrap}
.qs-rb-retry {
    padding:12px 24px;border-radius:var(--rsm);border:none;
    background:linear-gradient(135deg,var(--g600),var(--t400));
    color:#fff;font-weight:800;font-size:.92rem;cursor:pointer;
    transition:all .2s;font-family:'DM Sans',sans-serif;
}
.qs-rb-retry:hover{transform:translateY(-2px);box-shadow:var(--sh-lg)}
.qs-rb-back {
    padding:12px 24px;border-radius:var(--rsm);
    border:2px solid var(--bd);background:var(--sf);
    font-weight:700;font-size:.92rem;cursor:pointer;
    transition:all .2s;text-decoration:none;color:var(--ink3);
    font-family:'DM Sans',sans-serif;display:inline-flex;align-items:center;gap:6px;
}
.qs-rb-back:hover{background:var(--g100)}

/* ============================================================
   REVIEW LIST
   ============================================================ */
.qs-review-header {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:1rem;font-weight:800;margin-bottom:12px;
    display:flex;align-items:center;gap:8px;
}
.qs-review-list {display:flex;flex-direction:column;gap:10px;margin-bottom:32px}
.qs-review-item {
    background:var(--sf);border:2px solid var(--bd);
    border-radius:var(--rsm);padding:16px 18px;
    animation:fadeUp .35s ease both;
}
.qs-review-top {
    display:flex;align-items:flex-start;justify-content:space-between;gap:10px;
    margin-bottom:10px;
}
.qs-review-q {font-size:.9rem;font-weight:700;line-height:1.4}
.qs-review-ans {font-size:.8rem;color:var(--ink3);margin-top:4px}

/* ============================================================
   GURU PREVIEW
   ============================================================ */
.qs-guru-list {display:flex;flex-direction:column;gap:14px}
.qs-guru-q-card {
    background:var(--sf);border:2px solid var(--bd);border-radius:var(--r);
    padding:24px 26px;animation:fadeUp .35s ease both;
}
.qs-guru-q-header {
    display:flex;align-items:flex-start;justify-content:space-between;
    gap:10px;margin-bottom:16px;
}
.qs-guru-q-title {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:.9rem;font-weight:800;
}
.qs-guru-q-sub {font-size:.78rem;color:var(--ink3);margin-top:2px}
.qs-attempt-table-wrap {
    background:var(--sf);border:2px solid var(--bd);border-radius:var(--r);
    overflow:hidden;margin:18px 0 24px;animation:fadeUp .35s ease both;
}
.qs-attempt-table {width:100%;border-collapse:collapse;font-size:.84rem}
.qs-attempt-table th,.qs-attempt-table td {padding:11px 14px;border-bottom:1px solid var(--g100);text-align:left;vertical-align:top}
.qs-attempt-table th {background:var(--sf2);font-size:.68rem;text-transform:uppercase;letter-spacing:.08em;color:var(--ink3)}
.qs-attempt-table tr:last-child td {border-bottom:0}
.qs-attempt-answer {font-size:.78rem;color:var(--ink3);margin-top:4px}

/* SECTION TITLE */
.qs-section-title {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:1rem;font-weight:800;margin-bottom:14px;
    display:flex;align-items:center;gap:8px;
    animation:fadeUp .4s ease both;
}

/* CONFETTI */
.qs-confetti {
    position:fixed;top:0;left:0;width:100%;height:100%;
    pointer-events:none;z-index:9999;overflow:hidden;
}
.qs-confetti-piece {
    position:absolute;top:-16px;width:10px;height:10px;
    border-radius:2px;animation:confettiFall 2s ease-in both;
}

/* HIDDEN */
.qs-hidden {display:none!important}

@media(max-width:640px){
    .qs-info-row{grid-template-columns:1fr 1fr}
    .qs-session-header{flex-direction:column}
    .qs-score-banner{flex-direction:column;align-items:flex-start}
    .qs-q-card{padding:18px}
}
@media(max-width:400px){
    .qs-info-row{grid-template-columns:1fr}
}
</style>
@endpush

@section('actions')
    @if ($role === 'siswa')
        <a class="btn btn-primary"
           href="{{ route('siswa.ai.index', array_filter(['subject' => $subject->id, 'material' => $material->id, 'quiz' => $quiz->id, 'attempt' => $latestAttempt?->id])) }}">
            🤖 Tanya AI
        </a>
    @endif
    @if ($role === 'guru')
        <a class="btn btn-soft" href="{{ route('guru.materials.quizzes.create', [$subject, $material]) }}">
            ＋ Buat Kuis Baru
        </a>
    @endif
    <a class="btn btn-soft" href="{{ route('materials.show', [$subject, $material]) }}">← Kembali</a>
@endsection

@section('heading', $quiz->title)

@section('content')
<div class="qs-wrap">

    {{-- ============================================================
         SESSION HEADER
         ============================================================ --}}
    <div class="qs-session-header">
        <div>
            <div class="qs-session-topic">{{ $quiz->title }}</div>
            <div class="qs-session-meta">{{ $material->title }} · {{ $subject->name }}</div>
        </div>
        <div class="qs-pill-row">
            <span class="qs-pill">{{ $quiz->questions->count() }} Soal</span>
            <span class="qs-pill {{ $diffClass }}">{{ $diffLabel }}</span>
            <span class="qs-pill">{{ $quiz->duration }} menit</span>
        </div>
    </div>

    {{-- ============================================================
         INFO CARDS
         ============================================================ --}}
    <div class="qs-info-row">
        <div class="qs-info-card">
            <div class="qs-info-label">Mata Pelajaran</div>
            <div class="qs-info-val">{{ $subject->name }}</div>
        </div>
        <div class="qs-info-card">
            <div class="qs-info-label">Dibuat Oleh</div>
            <div class="qs-info-val">{{ $quiz->creator?->name ?? '—' }}</div>
        </div>
        <div class="qs-info-card">
            <div class="qs-info-label">Materi</div>
            <div class="qs-info-val">{{ $material->title }}</div>
        </div>
    </div>

    {{-- ============================================================
         DESCRIPTION
         ============================================================ --}}
    @if ($quiz->description)
        <div class="qs-desc">
            <div class="qs-desc-label">📋 Instruksi</div>
            <div class="qs-desc-text">{{ $quiz->description }}</div>
        </div>
    @endif

    {{-- ============================================================
         GURU VIEW — preview soal
         ============================================================ --}}
    @if ($role === 'guru')
        <div class="qs-section-title">📝 Preview Soal</div>
        <div class="qs-guru-list">
            @foreach ($quiz->questions as $question)
                <div class="qs-guru-q-card" style="animation-delay:{{ $loop->index * 0.06 }}s">
                    <div class="qs-guru-q-header">
                        <div>
                            <div class="qs-guru-q-title">Soal {{ $question->position }}</div>
                            <div class="qs-guru-q-sub">Pilihan ganda · Klik untuk lihat detail</div>
                        </div>
                        <span class="qs-key-pill">Kunci: {{ strtoupper($question->correct_option) }}</span>
                    </div>

                    @if ($question->image_source)
                        <img class="qs-q-img" src="{{ $question->image_source }}" alt="Gambar soal {{ $question->position }}"/>
                    @endif

                    <div class="qs-q-text" style="margin-bottom:16px;font-size:1rem">{{ $question->question }}</div>

                    <div class="qs-choice-list">
                        @foreach ($question->options as $key => $option)
                            <div class="qs-choice {{ $question->correct_option === $key ? 'qs-choice-correct' : '' }}">
                                <span class="qs-opt-letter" style="{{ $question->correct_option === $key ? 'background:var(--g500);color:#fff' : '' }}">
                                    {{ strtoupper($key) }}
                                </span>
                                {{ $option }}
                                @if ($question->correct_option === $key)
                                    <span style="margin-left:auto;font-size:.75rem;font-weight:800;color:var(--g600)">✅ Benar</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if ($question->explanation)
                        <div class="qs-explanation visible" style="margin-top:12px">
                            <div class="qs-exp-label">💡 Pembahasan</div>
                            <div class="qs-exp-text">{{ $question->explanation }}</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="qs-section-title" style="margin-top:22px">Result Kuis Siswa</div>
        @if (($quizAttempts ?? collect())->isEmpty())
            <div class="qs-desc">
                <div class="qs-desc-text">Belum ada siswa yang mengerjakan kuis ini.</div>
            </div>
        @else
            <div class="qs-attempt-table-wrap">
                <table class="qs-attempt-table">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Skor</th>
                            <th>Benar</th>
                            <th>Waktu</th>
                            <th>Ringkasan Jawaban</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quizAttempts as $attempt)
                            <tr>
                                <td>
                                    <strong>{{ $attempt->user?->name ?? 'Siswa' }}</strong>
                                    <div class="qs-attempt-answer">{{ $attempt->user?->email }}</div>
                                </td>
                                <td><span class="qs-key-pill">Skor {{ $attempt->score }}</span></td>
                                <td>{{ $attempt->correct_answers }}/{{ $attempt->total_questions }}</td>
                                <td>{{ $attempt->submitted_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td>
                                    @foreach ($attempt->answers->sortBy(fn ($answer) => $answer->question?->position) as $answer)
                                        <div class="qs-attempt-answer">
                                            Soal {{ $answer->question?->position }}: Jawaban siswa: {{ strtoupper($answer->selected_option) }}
                                            ({{ $answer->is_correct ? 'Benar' : 'Salah' }})
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    <a class="qs-btn-soft" target="_blank" href="{{ route('quizzes.attempts.print', [$subject, $material, $quiz, $attempt]) }}">Print PDF</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    {{-- ============================================================
         SISWA VIEW
         ============================================================ --}}
    @else

        {{-- SCORE BANNER (jika sudah pernah mengerjakan) --}}
        @if ($latestAttempt)
            <div class="qs-score-banner" id="qs-score-banner">
                <div class="qs-score-circle">
                    <div class="qs-score-num">{{ $latestAttempt->score }}</div>
                    <div class="qs-score-pct">poin</div>
                </div>
                <div class="qs-score-info">
                    <div class="qs-score-title">
                        @if ($latestAttempt->score >= 80) 🎉 Luar biasa!
                        @elseif ($latestAttempt->score >= 60) 👍 Hasil yang baik!
                        @else 📚 Terus semangat!
                        @endif
                    </div>
                    <div class="qs-score-sub">
                        Benar {{ $latestAttempt->correct_answers }} dari {{ $latestAttempt->total_questions }} soal ·
                        {{ $latestAttempt->submitted_at?->diffForHumans() ?? '-' }}
                    </div>
                    <div class="qs-score-actions">
                        <a href="{{ route('quizzes.attempts.print', [$subject, $material, $quiz, $latestAttempt]) }}"
                           target="_blank" class="qs-btn-soft">🖨️ Print PDF</a>
                        <a href="{{ route('siswa.ai.index', array_filter(['subject' => $subject->id, 'material' => $material->id, 'quiz' => $quiz->id, 'attempt' => $latestAttempt->id])) }}"
                           class="qs-btn-soft">🤖 Bahas dengan AI</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- QUIZ FORM (interactive mode) --}}
        <div id="qs-session-wrap">

            {{-- Progress bar --}}
            <div class="qs-progress-wrap">
                <div class="qs-progress-top">
                    <span id="qs-progress-label">Soal 1 dari {{ $quiz->questions->count() }}</span>
                    <span class="qs-score-live" id="qs-score-live">0 poin</span>
                </div>
                <div class="qs-progress-track">
                    <div class="qs-progress-fill" id="qs-progress-fill" style="width:0%"></div>
                </div>
                <div class="qs-step-dots" id="qs-step-dots"></div>
            </div>

            {{-- Question card --}}
            <div class="qs-q-card" id="qs-q-card">
                <div class="qs-q-top">
                    <div class="qs-q-num-badge" id="qs-q-num">1</div>
                    <div class="qs-q-text" id="qs-q-text"></div>
                </div>
                <img class="qs-q-img" id="qs-q-img" src="" alt="" style="display:none"/>
                <div class="qs-options" id="qs-options"></div>
                <div class="qs-explanation" id="qs-explanation">
                    <div class="qs-exp-label">💡 Pembahasan</div>
                    <div class="qs-exp-text" id="qs-exp-text"></div>
                </div>
            </div>

            {{-- Action row --}}
            <div class="qs-action-row">
                <div class="qs-action-left">
                    <button class="qs-btn-prev" id="qs-btn-prev" onclick="doPrev()">← Sebelumnya</button>
                    <button class="qs-btn-skip" id="qs-btn-skip" onclick="doSkip()">Lewati</button>
                </div>
                <div class="qs-action-right">
                    <button class="qs-btn-next" id="qs-btn-next" onclick="doNext()">
                        <span id="qs-btn-next-label">Lanjut →</span>
                    </button>
                </div>
            </div>

        </div>

        {{-- RESULT (hidden until quiz done) --}}
        <div id="qs-result" class="qs-hidden">

            <div class="qs-result-hero">
                <span class="qs-result-emoji" id="qs-result-emoji">🎉</span>
                <div class="qs-result-title" id="qs-result-title">Selesai!</div>
                <div class="qs-result-sub"   id="qs-result-sub">Kamu berhasil menyelesaikan kuis.</div>
                <span class="qs-score-big"   id="qs-score-big">—</span>
                <div class="qs-score-label"  id="qs-score-label">dari total poin</div>

                <div class="qs-result-stats">
                    <div class="qs-rs-item"><div class="qs-rs-val" id="rs-correct">0</div><div class="qs-rs-lbl">✅ Benar</div></div>
                    <div class="qs-rs-item"><div class="qs-rs-val" id="rs-wrong">0</div><div class="qs-rs-lbl">❌ Salah</div></div>
                    <div class="qs-rs-item"><div class="qs-rs-val" id="rs-skip">0</div><div class="qs-rs-lbl">⏭️ Lewati</div></div>
                </div>

                <div class="qs-result-btn-row">
                    <button class="qs-rb-retry" onclick="submitToServer()">💾 Simpan & Submit</button>
                    <button class="qs-rb-retry" onclick="doRetry()" style="background:var(--sf2);color:var(--ink3);border:2px solid var(--bd)">🔄 Ulangi</button>
                    <a href="{{ route('materials.show', [$subject, $material]) }}" class="qs-rb-back">← Kembali</a>
                </div>
            </div>

            <div class="qs-review-header">📋 Ulasan Jawaban</div>
            <div class="qs-review-list" id="qs-review-list"></div>
        </div>

        {{-- HIDDEN FORM untuk submit ke server --}}
        <form id="qs-submit-form" method="POST"
              action="{{ route('quizzes.submit', [$subject, $material, $quiz]) }}"
              style="display:none">
            @csrf
            <div id="qs-form-answers"></div>
        </form>

        {{-- REVIEW ATTEMPT (jika sudah ada attempt) --}}
        @if ($latestAttempt)
            <div class="qs-section-title" style="margin-top:32px">📊 Hasil Pengerjaan Terakhir</div>
            <div class="qs-review-list">
                @foreach ($resultAnswers as $answer)
                    <div class="qs-review-item" style="animation-delay:{{ $loop->index * 0.06 }}s">
                        <div class="qs-review-top">
                            <div>
                                <div class="qs-review-q">Soal {{ $answer->question->position }}: {{ $answer->question->question }}</div>
                                <div class="qs-review-ans">
                                    Jawaban kamu: <strong>{{ strtoupper($answer->selected_option) }}</strong> ·
                                    Jawaban benar: <strong>{{ strtoupper($answer->question->correct_option) }}</strong>
                                </div>
                            </div>
                            <span class="qs-ans-badge {{ $answer->is_correct ? 'qs-ans-correct' : 'qs-ans-wrong' }}">
                                {{ $answer->is_correct ? '✅ Benar' : '❌ Salah' }}
                            </span>
                        </div>

                        @if ($answer->question->image_source)
                            <img class="qs-q-img" src="{{ $answer->question->image_source }}" alt="" style="margin-bottom:10px"/>
                        @endif

                        @if (! $answer->is_correct && $answer->question->explanation)
                            <div class="qs-explanation visible">
                                <div class="qs-exp-label">💡 Pembahasan</div>
                                <div class="qs-exp-text">{{ $answer->question->explanation }}</div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($wrongAnswers->isEmpty())
                <div style="background:var(--g100);border:2px solid var(--g400);border-radius:var(--rsm);padding:16px 20px;font-weight:700;color:var(--g700);text-align:center;margin-bottom:24px">
                    🏆 Semua jawaban pada percobaan terakhir sudah benar!
                </div>
            @endif
        @endif

    @endif

</div>

{{-- CONFETTI --}}
<div class="qs-confetti" id="qs-confetti"></div>

@endsection

@push('scripts')
<script>
/* ================================================================
   DATA FROM BLADE
   ================================================================ */
const QS_QUESTIONS = @json($questionsJson);

const OPT_KEYS   = ['a','b','c','d'];
const LETTERS    = ['A','B','C','D'];

/* ================================================================
   STATE
   ================================================================ */
let state = {
    current:  0,
    score:    0,
    correct:  0,
    wrong:    0,
    skipped:  0,
    // history per soal: null | {chosen, isCorrect}
    history:  new Array(QS_QUESTIONS.length).fill(null),
    answered: false,
};

/* ================================================================
   INIT
   ================================================================ */
(function init(){
    if(!QS_QUESTIONS.length) return;
    buildDots();
    renderQuestion();
})();

/* ================================================================
   STEP DOTS
   ================================================================ */
function buildDots(){
    const wrap = document.getElementById('qs-step-dots');
    if(!wrap) return;
    wrap.innerHTML = '';
    QS_QUESTIONS.forEach((_,i) => {
        const dot = document.createElement('div');
        dot.className = 'qs-dot';
        dot.id = 'dot-'+i;
        wrap.appendChild(dot);
    });
    refreshDots();
}

function refreshDots(){
    QS_QUESTIONS.forEach((_,i) => {
        const dot = document.getElementById('dot-'+i);
        if(!dot) return;
        dot.className = 'qs-dot';
        const h = state.history[i];
        if(i === state.current) dot.classList.add('current');
        else if(!h) dot.classList.add(''); // unanswered — base style
        else if(h.chosen === null) dot.classList.add('done-skip');
        else if(h.isCorrect) dot.classList.add('done-correct');
        else dot.classList.add('done-wrong');
    });
}

/* ================================================================
   RENDER QUESTION
   ================================================================ */
function renderQuestion(){
    const q = QS_QUESTIONS[state.current];
    if(!q) return;

    // re-animate card
    const card = document.getElementById('qs-q-card');
    card.style.animation = 'none';
    void card.offsetWidth;
    card.style.animation = 'fadeUp .35s ease both';

    document.getElementById('qs-q-num').textContent  = q.position;
    document.getElementById('qs-q-text').textContent = q.question;
    document.getElementById('qs-progress-label').textContent =
        `Soal ${state.current+1} dari ${QS_QUESTIONS.length}`;

    const answered = state.history.filter(h => h !== null).length;
    document.getElementById('qs-progress-fill').style.width =
        ((answered / QS_QUESTIONS.length) * 100) + '%';

    // image
    const imgEl = document.getElementById('qs-q-img');
    if(q.image_source){
        imgEl.src = q.image_source;
        imgEl.style.display = 'block';
    } else {
        imgEl.style.display = 'none';
    }

    // options
    const optsEl = document.getElementById('qs-options');
    optsEl.innerHTML = '';
    const h = state.history[state.current];
    const alreadyAnswered = h !== null;

    Object.keys(q.options).forEach(key => {
        const btn = document.createElement('button');
        btn.className = 'qs-option';
        btn.type = 'button';
        btn.innerHTML = `<span class="qs-opt-letter">${key.toUpperCase()}</span>${q.options[key]}`;

        if(alreadyAnswered){
            btn.disabled = true;
            if(key === q.correct_option) btn.classList.add('correct');
            if(h.chosen && h.chosen === key && !h.isCorrect) btn.classList.add('wrong');
        } else {
            btn.onclick = () => doAnswer(key);
        }
        optsEl.appendChild(btn);
    });

    // explanation
    const expEl  = document.getElementById('qs-explanation');
    const expTxt = document.getElementById('qs-exp-text');
    if(alreadyAnswered && q.explanation){
        expTxt.textContent = q.explanation;
        expEl.classList.add('visible');
    } else {
        expEl.classList.remove('visible');
        expTxt.textContent = '';
    }

    // buttons
    updateButtons();
    refreshDots();
}

/* ================================================================
   UPDATE BUTTONS
   ================================================================ */
function updateButtons(){
    const h           = state.history[state.current];
    const answered    = h !== null;
    const isLast      = state.current === QS_QUESTIONS.length - 1;
    const allAnswered = state.history.every(x => x !== null);

    // prev
    const prevBtn = document.getElementById('qs-btn-prev');
    prevBtn.disabled = state.current === 0;

    // skip — disable jika sudah dijawab
    const skipBtn = document.getElementById('qs-btn-skip');
    skipBtn.disabled = answered;

    // next label & style
    const nextBtn   = document.getElementById('qs-btn-next');
    const nextLabel = document.getElementById('qs-btn-next-label');
    if(isLast && allAnswered){
        nextLabel.textContent = '✅ Selesai & Submit';
        nextBtn.classList.add('finish');
    } else if(isLast){
        nextLabel.textContent = answered ? '📋 Lihat Hasil' : 'Lanjut →';
        nextBtn.classList.toggle('finish', answered);
    } else {
        nextLabel.textContent = 'Lanjut →';
        nextBtn.classList.remove('finish');
    }
    // score live
    document.getElementById('qs-score-live').textContent = state.score + ' poin';
}

/* ================================================================
   ANSWER
   ================================================================ */
function doAnswer(chosenKey){
    if(state.history[state.current] !== null) return;

    const q = QS_QUESTIONS[state.current];
    const isCorrect = chosenKey === q.correct_option;

    state.history[state.current] = {chosen: chosenKey, isCorrect};

    if(isCorrect){
        state.score   += 20;
        state.correct++;
        const liveEl = document.getElementById('qs-score-live');
        liveEl.classList.remove('bump');
        void liveEl.offsetWidth;
        liveEl.classList.add('bump');
    } else {
        state.wrong++;
    }

    // visual feedback
    document.querySelectorAll('.qs-option').forEach(btn => {
        btn.disabled = true;
        const letter = btn.querySelector('.qs-opt-letter').textContent.toLowerCase();
        if(letter === q.correct_option) btn.classList.add('correct');
        if(letter === chosenKey && !isCorrect) btn.classList.add('wrong');
    });

    if(q.explanation){
        document.getElementById('qs-exp-text').textContent = q.explanation;
        document.getElementById('qs-explanation').classList.add('visible');
    }

    updateButtons();
    refreshDots();
}

/* ================================================================
   SKIP
   ================================================================ */
function doSkip(){
    if(state.history[state.current] !== null) return;

    const q = QS_QUESTIONS[state.current];
    state.history[state.current] = {chosen: null, isCorrect: false};
    state.skipped++;

    document.querySelectorAll('.qs-option').forEach(btn => {
        btn.disabled = true;
        const letter = btn.querySelector('.qs-opt-letter').textContent.toLowerCase();
        if(letter === q.correct_option) btn.classList.add('correct');
    });

    if(q.explanation){
        document.getElementById('qs-exp-text').textContent = q.explanation;
        document.getElementById('qs-explanation').classList.add('visible');
    }

    updateButtons();
    refreshDots();
}

/* ================================================================
   PREV
   ================================================================ */
function doPrev(){
    if(state.current === 0) return;
    state.current--;
    renderQuestion();
}

/* ================================================================
   NEXT
   ================================================================ */
function doNext(){
    const allAnswered = state.history.every(x => x !== null);
    const isLast      = state.current === QS_QUESTIONS.length - 1;

    if(isLast && allAnswered){
        showResult();
        return;
    }

    // Jika soal terakhir tapi belum semua dijawab, cari soal pertama yg belum dijawab
    if(isLast){
        const firstUnanswered = state.history.findIndex(x => x === null);
        if(firstUnanswered !== -1){
            state.current = firstUnanswered;
            renderQuestion();
        }
        return;
    }

    state.current++;
    renderQuestion();
}

/* ================================================================
   RESULT
   ================================================================ */
function showResult(){
    const pct = QS_QUESTIONS.length
        ? Math.round((state.correct / QS_QUESTIONS.length) * 100)
        : 0;

    document.getElementById('qs-session-wrap').classList.add('qs-hidden');
    document.getElementById('qs-result').classList.remove('qs-hidden');

    let emoji, title, sub;
    if(pct===100){ emoji='🏆'; title='Sempurna!'; sub='Nilai 100%! Luar biasa!'; launchConfetti(); }
    else if(pct>=80){ emoji='🎉'; title='Luar Biasa!'; sub='Hampir sempurna! Terus latihan.'; launchConfetti(); }
    else if(pct>=60){ emoji='👍'; title='Bagus!'; sub='Hasil yang baik! Masih ada ruang berkembang.'; }
    else if(pct>=40){ emoji='📚'; title='Terus Semangat!'; sub='Coba lagi ya! Latihan lebih banyak.'; }
    else { emoji='💪'; title='Jangan Menyerah!'; sub='Setiap ahli pernah jadi pemula. Ayo coba lagi!'; }

    document.getElementById('qs-result-emoji').textContent = emoji;
    document.getElementById('qs-result-title').textContent = title;
    document.getElementById('qs-result-sub').textContent   = sub;
    document.getElementById('qs-score-big').textContent    = pct + '%';
    document.getElementById('qs-score-label').textContent  =
        `${state.score} dari ${QS_QUESTIONS.length * 20} poin`;
    document.getElementById('rs-correct').textContent = state.correct;
    document.getElementById('rs-wrong').textContent   = state.wrong;
    document.getElementById('rs-skip').textContent    = state.skipped;

    // build review
    const reviewEl = document.getElementById('qs-review-list');
    reviewEl.innerHTML = '';
    QS_QUESTIONS.forEach((q, i) => {
        const h = state.history[i];
        const isCorrect = h?.isCorrect;
        const chosen    = h?.chosen;
        const item = document.createElement('div');
        item.className = 'qs-review-item';

        let badge, icon;
        if(!chosen){ badge='qs-ans-skip'; icon='⏭️ Dilewati'; }
        else if(isCorrect){ badge='qs-ans-correct'; icon='✅ Benar'; }
        else { badge='qs-ans-wrong'; icon='❌ Salah'; }

        item.innerHTML = `
            <div class="qs-review-top">
                <div>
                    <div class="qs-review-q">Soal ${q.position}: ${q.question}</div>
                    <div class="qs-review-ans">
                        Jawaban kamu: <strong>${chosen ? chosen.toUpperCase() : '—'}</strong> ·
                        Jawaban benar: <strong>${q.correct_option.toUpperCase()}</strong>
                    </div>
                </div>
                <span class="qs-ans-badge ${badge}">${icon}</span>
            </div>
            ${q.explanation && !isCorrect ? `
            <div class="qs-explanation visible">
                <div class="qs-exp-label">💡 Pembahasan</div>
                <div class="qs-exp-text">${q.explanation}</div>
            </div>` : ''}
        `;
        reviewEl.appendChild(item);
    });

    window.scrollTo({top:0,behavior:'smooth'});
}

/* ================================================================
   SUBMIT TO SERVER
   ================================================================ */
function submitToServer(){
    const formAnswers = document.getElementById('qs-form-answers');
    formAnswers.innerHTML = '';

    QS_QUESTIONS.forEach((q, i) => {
        const h = state.history[i];
        if(h?.chosen){
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = `answers[${q.id}]`;
            input.value = h.chosen;
            formAnswers.appendChild(input);
        }
    });

    document.getElementById('qs-submit-form').submit();
}

/* ================================================================
   RETRY — FIX: clear DOM dulu, baru render ulang setelah timeout
   ================================================================ */
function doRetry(){
    // Reset semua state
    state = {
        current:  0,
        score:    0,
        correct:  0,
        wrong:    0,
        skipped:  0,
        history:  new Array(QS_QUESTIONS.length).fill(null),
        answered: false,
    };

    // Sembunyikan result, tampilkan session
    document.getElementById('qs-result').classList.add('qs-hidden');
    document.getElementById('qs-session-wrap').classList.remove('qs-hidden');

    // Sembunyikan score banner attempt sebelumnya
    const scoreBanner = document.getElementById('qs-score-banner');
    if(scoreBanner) scoreBanner.classList.add('qs-hidden');

    // Reset progress UI
    document.getElementById('qs-score-live').textContent = '0 poin';
    document.getElementById('qs-progress-fill').style.width = '0%';
    document.getElementById('qs-progress-label').textContent =
        `Soal 1 dari ${QS_QUESTIONS.length}`;

    // === FIX: Bersihkan konten lama dari DOM sebelum render ulang ===
    document.getElementById('qs-q-num').textContent  = '';
    document.getElementById('qs-q-text').textContent = '';
    document.getElementById('qs-options').innerHTML  = '';
    document.getElementById('qs-q-img').style.display = 'none';
    document.getElementById('qs-explanation').classList.remove('visible');
    document.getElementById('qs-exp-text').textContent = '';

    // Rebuild dots
    buildDots();

    // === FIX: Beri browser waktu untuk commit semua perubahan visibility
    //          sebelum renderQuestion() dipanggil, agar animasi & konten
    //          tidak bentrok dengan reflow dari display:none -> display:block ===
    setTimeout(() => {
        renderQuestion();
        window.scrollTo({top: 0, behavior: 'smooth'});
    }, 50);
}

/* ================================================================
   CONFETTI
   ================================================================ */
function launchConfetti(){
    const container = document.getElementById('qs-confetti');
    container.innerHTML = '';
    const colors = ['#22c55e','#16a34a','#86efac','#0d9488','#fbbf24','#60a5fa','#f472b6'];
    for(let i=0;i<60;i++){
        const piece = document.createElement('div');
        piece.className = 'qs-confetti-piece';
        piece.style.cssText = `
            left:${Math.random()*100}%;
            background:${colors[Math.floor(Math.random()*colors.length)]};
            width:${6+Math.random()*8}px;height:${6+Math.random()*8}px;
            border-radius:${Math.random()>.5?'50%':'2px'};
            animation-delay:${Math.random()*1.5}s;
            animation-duration:${1.5+Math.random()}s;
        `;
        container.appendChild(piece);
        piece.addEventListener('animationend', () => piece.remove());
    }
}
</script>
@endpush
