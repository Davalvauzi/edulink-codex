@extends('layouts.portal')

@php
    $oldQuestions = old('questions', [
        ['question' => '', 'option_a' => '', 'option_b' => '', 'option_c' => '', 'option_d' => '', 'correct_option' => 'a', 'explanation' => ''],
    ]);
@endphp

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,400&display=swap" rel="stylesheet"/>
<style>
/* ============================================================
   DESIGN TOKENS
   ============================================================ */
.cq-wrap {
    --g100:#bbf7d0;--g200:#86efac;--g400:#22c55e;--g500:#16a34a;
    --g600:#15803d;--g700:#166534;--t400:#0d9488;--t600:#0f766e;
    --ink:#0d1f14;--ink2:#1e3a28;--ink3:#3d6b4f;
    --sf:#ffffff;--sf2:#f0faf4;--bd:#86efac;--bd2:#4ade80;
    --sh:0 2px 20px rgba(21,128,61,.18);--sh-lg:0 8px 48px rgba(21,128,61,.22);
    --r:14px;--rsm:9px;
    font-family:'DM Sans',sans-serif;
    color:var(--ink);
}

@keyframes cq-fadeUp {
    from{opacity:0;transform:translateY(18px)}
    to{opacity:1;transform:translateY(0)}
}
@keyframes cq-slideIn {
    from{opacity:0;transform:translateY(10px)}
    to{opacity:1;transform:translateY(0)}
}
@keyframes cq-toast {
    0%{opacity:0;transform:translateY(20px)}
    15%{opacity:1;transform:translateY(0)}
    80%{opacity:1;transform:translateY(0)}
    100%{opacity:0;transform:translateY(-10px)}
}

/* ============================================================
   PAGE HEADER
   ============================================================ */
.cq-page-header {
    display:flex;align-items:flex-start;justify-content:space-between;
    gap:16px;margin-bottom:28px;
    animation:cq-fadeUp .4s ease both;
}
.cq-page-title {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:1.5rem;font-weight:800;margin-bottom:4px;
}
.cq-page-sub { font-size:.875rem;color:var(--ink3) }
.cq-page-badge {
    display:flex;align-items:center;gap:6px;
    padding:6px 14px;border-radius:20px;
    background:var(--g100);border:1.5px solid var(--g400);
    font-size:.75rem;font-weight:700;color:var(--g700);
    white-space:nowrap;flex-shrink:0;
}
.cq-qcount {
    display:inline-flex;align-items:center;gap:6px;
    padding:4px 12px;border-radius:20px;
    background:var(--g100);border:1.5px solid var(--g400);
    font-size:.75rem;font-weight:700;color:var(--g700);margin-left:8px;
}

/* ============================================================
   CARD
   ============================================================ */
.cq-card {
    background:var(--sf);border:2px solid var(--bd);border-radius:var(--r);
    padding:26px 28px;margin-bottom:18px;
    animation:cq-fadeUp .4s ease both;
}
.cq-card-title {
    font-family:'Bricolage Grotesque',sans-serif;
    font-size:.95rem;font-weight:800;margin-bottom:18px;
    display:flex;align-items:center;gap:8px;
}
.cq-card-title-icon {
    width:32px;height:32px;border-radius:9px;
    background:var(--g100);display:flex;align-items:center;
    justify-content:center;font-size:1rem;
}

/* ============================================================
   FORM
   ============================================================ */
.cq-field { margin-bottom:16px }
.cq-field:last-child { margin-bottom:0 }
.cq-label {
    display:block;font-size:.8rem;font-weight:700;
    color:var(--ink2);margin-bottom:6px;
}
.cq-label-req { color:var(--g500);margin-left:2px }
.cq-input, .cq-select, .cq-textarea {
    width:100%;padding:11px 14px;
    border:2px solid var(--bd);border-radius:var(--rsm);
    background:var(--sf2);
    font-family:'DM Sans',sans-serif;font-size:.9rem;color:var(--ink);
    transition:all .2s;outline:none;
}
.cq-input:focus,.cq-select:focus,.cq-textarea:focus {
    border-color:var(--g400);background:var(--sf);
    box-shadow:0 0 0 3px rgba(22,163,74,.12);
}
.cq-input::placeholder,.cq-textarea::placeholder { color:#a7c4b0 }
.cq-textarea { resize:vertical;min-height:80px;line-height:1.6 }
.cq-select {
    cursor:pointer;appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='7' fill='none'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%233d6b4f' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat:no-repeat;background-position:right 14px center;padding-right:38px;
}
.cq-input.is-invalid,.cq-select.is-invalid,.cq-textarea.is-invalid {
    border-color:#fca5a5;
}
.cq-field-row   { display:grid;grid-template-columns:1fr 1fr;gap:14px }
.cq-field-row-3 { display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px }
.cq-field-row-4 { display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:14px }
.cq-error-msg { font-size:.72rem;color:#dc2626;margin-top:4px }
.cq-input-hint { font-size:.72rem;color:var(--ink3);margin-top:4px }

/* ============================================================
   ALERT (Laravel validation errors)
   ============================================================ */
.cq-alert-error {
    background:#fef2f2;border:2px solid #fca5a5;border-radius:var(--rsm);
    padding:14px 18px;margin-bottom:20px;
    font-size:.875rem;color:#dc2626;font-weight:600;
    animation:cq-fadeUp .3s ease both;
}
.cq-alert-error ul { margin:6px 0 0 16px }
.cq-alert-error li { margin-bottom:3px }

/* ============================================================
   TABS
   ============================================================ */
.cq-tabs-bar {
    display:flex;align-items:center;gap:8px;
    margin-bottom:20px;padding-bottom:4px;
    overflow-x:auto;scrollbar-width:none;
    animation:cq-fadeUp .4s ease both;
}
.cq-tabs-bar::-webkit-scrollbar { display:none }
.cq-tab {
    display:flex;align-items:center;gap:6px;
    padding:8px 16px;border-radius:10px;
    border:2px solid var(--bd);background:var(--sf);
    font-family:'DM Sans',sans-serif;font-size:.82rem;font-weight:700;
    color:var(--ink3);cursor:pointer;transition:all .2s;white-space:nowrap;flex-shrink:0;
}
.cq-tab:hover { background:var(--g100);border-color:var(--g400) }
.cq-tab.active { background:var(--g100);border-color:var(--g500);color:var(--g700) }
.cq-tab-del {
    width:16px;height:16px;border-radius:4px;
    background:rgba(220,38,38,.12);color:#dc2626;
    display:inline-flex;align-items:center;justify-content:center;
    font-size:.65rem;line-height:1;transition:all .2s;
}
.cq-tab:hover .cq-tab-del { background:#dc2626;color:#fff }
.cq-tab-add {
    padding:8px 14px;border-radius:10px;
    border:2px dashed var(--bd2);background:transparent;
    font-size:.82rem;font-weight:700;color:var(--g600);
    cursor:pointer;transition:all .2s;white-space:nowrap;flex-shrink:0;
}
.cq-tab-add:hover { background:var(--g100);border-color:var(--g400) }

/* ============================================================
   QUESTION PANEL
   ============================================================ */
.cq-qpanel { display:none }
.cq-qpanel.active { display:block }

/* ============================================================
   OPTIONS
   ============================================================ */
.cq-options-list { display:flex;flex-direction:column;gap:10px }
.cq-option-row {
    display:flex;align-items:center;gap:10px;
    padding:10px 14px;border-radius:var(--rsm);
    border:2px solid var(--bd);background:var(--sf2);
    transition:border-color .2s;
    animation:cq-slideIn .3s ease both;
}
.cq-option-row:hover { border-color:var(--g400) }
.cq-option-row.cq-correct-row {
    border-color:var(--g500);background:rgba(22,163,74,.06);
}
.cq-option-letter {
    width:30px;height:30px;border-radius:8px;flex-shrink:0;
    background:var(--g100);color:var(--g700);
    display:flex;align-items:center;justify-content:center;
    font-family:'Bricolage Grotesque',sans-serif;font-size:.82rem;font-weight:800;
}
.cq-correct-row .cq-option-letter { background:var(--g500);color:#fff }
.cq-option-input {
    flex:1;border:none;background:transparent;
    font-family:'DM Sans',sans-serif;font-size:.9rem;color:var(--ink);outline:none;
}
.cq-option-input::placeholder { color:#a7c4b0 }
.cq-correct-radio {
    display:flex;align-items:center;gap:5px;
    font-size:.75rem;font-weight:700;color:var(--ink3);
    cursor:pointer;flex-shrink:0;white-space:nowrap;
}
.cq-correct-radio input[type=radio] {
    accent-color:var(--g500);width:14px;height:14px;cursor:pointer;
}
.cq-correct-row .cq-correct-radio { color:var(--g600) }

/* ============================================================
   IMAGE UPLOAD
   ============================================================ */
.cq-img-upload-area {
    border:2px dashed var(--bd2);border-radius:var(--rsm);
    background:var(--sf2);padding:20px;text-align:center;
    cursor:pointer;transition:all .2s;position:relative;overflow:hidden;
}
.cq-img-upload-area:hover { border-color:var(--g400);background:var(--g100) }
.cq-img-upload-area.drag-over { border-color:var(--g500);background:rgba(22,163,74,.08) }
.cq-img-upload-input {
    position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;
}
.cq-img-upload-placeholder { pointer-events:none }
.cq-img-upload-icon { font-size:1.8rem;margin-bottom:6px }
.cq-img-upload-text { font-size:.82rem;font-weight:700;color:var(--ink3) }
.cq-img-upload-hint { font-size:.7rem;color:var(--ink3);margin-top:2px }
.cq-img-preview-wrap {
    position:relative;border-radius:var(--rsm);overflow:hidden;
    border:2px solid var(--g400);background:#000;display:none;
}
.cq-img-preview-wrap.has-img { display:block }
.cq-img-preview { width:100%;max-height:220px;object-fit:contain;display:block;background:var(--sf2) }
.cq-img-remove {
    position:absolute;top:8px;right:8px;width:30px;height:30px;border-radius:8px;
    background:rgba(220,38,38,.88);color:#fff;border:none;cursor:pointer;
    font-size:.8rem;display:flex;align-items:center;justify-content:center;transition:all .2s;
}
.cq-img-remove:hover { background:#dc2626;transform:scale(1.1) }
.cq-img-filename { font-size:.7rem;color:var(--ink3);padding:6px 10px;background:var(--sf);border-top:1px solid var(--bd) }

/* URL input row */
.cq-img-url-row { display:flex;align-items:center;gap:8px;margin-bottom:8px }
.cq-img-url-row .cq-input { margin-bottom:0 }
.cq-img-divider { font-size:.72rem;font-weight:700;color:var(--ink3);white-space:nowrap }

/* ============================================================
   EXPLANATION
   ============================================================ */
.cq-exp-wrap {
    border:2px solid #99f6e4;background:rgba(13,148,136,.05);
    border-radius:var(--rsm);padding:14px 16px;
}
.cq-exp-label {
    font-size:.7rem;font-weight:800;text-transform:uppercase;
    letter-spacing:.8px;color:var(--t600);margin-bottom:6px;
}

/* ============================================================
   PREVIEW
   ============================================================ */
.cq-preview-section { margin-bottom:18px;display:none;animation:cq-fadeUp .35s ease both }
.cq-preview-section.visible { display:block }
.cq-preview-label {
    font-family:'Bricolage Grotesque',sans-serif;font-size:1rem;
    font-weight:800;margin-bottom:12px;display:flex;align-items:center;gap:8px;
}
.cq-preview-card {
    background:var(--sf);border:2px solid var(--g400);border-radius:var(--r);
    padding:28px;position:relative;overflow:hidden;
}
.cq-preview-card::before {
    content:'PREVIEW';position:absolute;top:12px;right:14px;
    font-size:.6rem;font-weight:800;letter-spacing:1.5px;color:var(--g400);opacity:.45;
}
.cq-pv-num {
    display:inline-flex;align-items:center;justify-content:center;
    width:36px;height:36px;border-radius:10px;background:var(--g100);
    font-family:'Bricolage Grotesque',sans-serif;font-size:.9rem;font-weight:800;
    color:var(--g700);margin-bottom:14px;
}
.cq-pv-img { width:100%;max-height:200px;object-fit:contain;border-radius:var(--rsm);margin-bottom:14px;border:2px solid var(--bd);display:none }
.cq-pv-img.has-img { display:block }
.cq-pv-q { font-family:'Bricolage Grotesque',sans-serif;font-size:1.1rem;font-weight:700;line-height:1.5;margin-bottom:18px }
.cq-pv-options { display:flex;flex-direction:column;gap:9px }
.cq-pv-opt { display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:var(--rsm);border:2px solid var(--bd);background:var(--sf2);font-size:.9rem;font-weight:600 }
.cq-pv-opt.cq-pv-correct { background:#dcfce7;border-color:var(--g500) }
.cq-pv-opt-letter { width:28px;height:28px;border-radius:7px;flex-shrink:0;background:var(--bd);color:var(--g700);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.82rem }
.cq-pv-correct .cq-pv-opt-letter { background:var(--g500);color:#fff }
.cq-pv-exp { margin-top:14px;border-radius:var(--rsm);border:2px solid #99f6e4;background:rgba(13,148,136,.07);padding:12px 16px }
.cq-pv-exp-label { font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--t600);margin-bottom:4px }
.cq-pv-exp-text { font-size:.85rem;color:var(--ink2) }

/* ============================================================
   FOOTER
   ============================================================ */
.cq-footer-actions {
    display:flex;align-items:center;justify-content:space-between;
    gap:12px;padding:20px 0 8px;border-top:2px solid var(--bd);margin-top:8px;
    flex-wrap:wrap;
}
.cq-footer-left  { display:flex;align-items:center;gap:10px }
.cq-footer-right { display:flex;align-items:center;gap:10px }

.cq-btn-ghost {
    padding:10px 20px;border-radius:var(--rsm);
    border:2px solid var(--bd);background:var(--sf);
    font-family:'DM Sans',sans-serif;font-weight:700;font-size:.875rem;
    color:var(--ink3);cursor:pointer;transition:all .2s;text-decoration:none;
}
.cq-btn-ghost:hover { background:var(--g100);color:var(--ink);border-color:var(--g400) }
.cq-btn-preview {
    padding:10px 20px;border-radius:var(--rsm);
    border:2px solid var(--g400);background:var(--g100);
    font-family:'DM Sans',sans-serif;font-weight:700;font-size:.875rem;
    color:var(--g700);cursor:pointer;transition:all .2s;
    display:flex;align-items:center;gap:7px;
}
.cq-btn-preview:hover { background:var(--g200) }
.cq-btn-save {
    padding:10px 28px;border-radius:var(--rsm);
    background:linear-gradient(135deg,var(--g600),var(--t400));
    border:none;color:#fff;
    font-family:'Bricolage Grotesque',sans-serif;font-weight:800;font-size:.92rem;
    cursor:pointer;transition:all .25s;
    display:flex;align-items:center;gap:8px;box-shadow:var(--sh);
}
.cq-btn-save:hover { transform:translateY(-2px);box-shadow:var(--sh-lg) }

/* ============================================================
   TOAST
   ============================================================ */
.cq-toast {
    position:fixed;bottom:32px;right:28px;z-index:9999;
    background:#1e3a28;color:#fff;padding:13px 20px;border-radius:var(--rsm);
    font-size:.875rem;font-weight:700;display:flex;align-items:center;gap:9px;
    box-shadow:var(--sh-lg);animation:cq-toast 3s ease forwards;pointer-events:none;
}
.cq-toast-success { background:var(--g600) }
.cq-toast-error   { background:#dc2626 }

@media(max-width:640px) {
    .cq-field-row,.cq-field-row-3,.cq-field-row-4 { grid-template-columns:1fr }
    .cq-footer-actions { flex-direction:column }
    .cq-btn-save { width:100%;justify-content:center }
}
</style>
@endpush

@section('sidebar')
    <a href="{{ route('materials.show', [$subject, $material]) }}">
        Kembali ke Detail Materi
        <span>{{ $material->title }}</span>
    </a>
    <div class="static-item">
        Mata Pelajaran
        <span>{{ $subject->name }}</span>
    </div>
    <div class="static-item">
        Mode
        <span>Buat kuis pilihan ganda</span>
    </div>
@endsection

@section('heading', 'Buat Kuis Baru')
@section('subtitle', 'Tambahkan latihan soal pilihan ganda. Setiap soal bisa diberi gambar dan pembahasan.')

@section('actions')
    <a class="btn btn-soft" href="{{ route('materials.show', [$subject, $material]) }}">Kembali</a>
@endsection

@section('content')
<div class="cq-wrap">

    {{-- ============================================================
         VALIDATION ERRORS
         ============================================================ --}}
    @if ($errors->any())
        <div class="cq-alert-error">
            <strong>Ada beberapa kesalahan yang perlu diperbaiki:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        id="cq-form"
        method="POST"
        action="{{ route('guru.materials.quizzes.store', [$subject, $material]) }}"
        enctype="multipart/form-data"
    >
        @csrf

        {{-- ============================================================
             PAGE HEADER
             ============================================================ --}}
        <div class="cq-page-header">
            <div>
                <div class="cq-page-title">
                    Buat Kuis
                    <span class="cq-qcount" id="qcount-badge">1 Soal</span>
                </div>
                <div class="cq-page-sub">
                    Kuis untuk materi <strong>{{ $material->title }}</strong> · {{ $subject->name }}
                </div>
            </div>
            <div class="cq-page-badge">✏️ {{ $subject->name }}</div>
        </div>

        {{-- ============================================================
             CARD: INFORMASI UMUM KUIS
             ============================================================ --}}
        <div class="cq-card" style="animation-delay:0s">
            <div class="cq-card-title">
                <div class="cq-card-title-icon">📋</div>
                Informasi Umum Kuis
            </div>

            <div class="cq-field-row" style="margin-bottom:14px">
                <div class="cq-field" style="margin-bottom:0">
                    <label class="cq-label" for="title">
                        Judul Kuis <span class="cq-label-req">*</span>
                    </label>
                    <input
                        class="cq-input @error('title') is-invalid @enderror"
                        id="title" name="title" type="text"
                        value="{{ old('title') }}"
                        placeholder="cth. Latihan Bab 1 – Tenses"
                        required
                    />
                    @error('title')
                        <div class="cq-error-msg">{{ $message }}</div>
                    @enderror
                </div>
                <div class="cq-field" style="margin-bottom:0">
                    <label class="cq-label" for="description">Instruksi / Deskripsi</label>
                    <input
                        class="cq-input @error('description') is-invalid @enderror"
                        id="description" name="description" type="text"
                        value="{{ old('description') }}"
                        placeholder="Tulis petunjuk singkat untuk siswa (opsional)"
                    />
                    @error('description')
                        <div class="cq-error-msg">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="cq-field-row">
                <div class="cq-field" style="margin-bottom:0">
                    <label class="cq-label" for="difficulty">
                        Tingkat Kesulitan <span class="cq-label-req">*</span>
                    </label>
                    <select
                        class="cq-select @error('difficulty') is-invalid @enderror"
                        id="difficulty" name="difficulty" required
                    >
                        <option value="">-- Pilih Tingkat --</option>
                        @foreach (['mudah' => 'Mudah', 'sedang' => 'Sedang', 'susah' => 'Susah'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('difficulty', 'sedang') === $val)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('difficulty')
                        <div class="cq-error-msg">{{ $message }}</div>
                    @enderror
                </div>
                <div class="cq-field" style="margin-bottom:0">
                    <label class="cq-label" for="duration">
                        Durasi Pengerjaan (menit) <span class="cq-label-req">*</span>
                    </label>
                    <input
                        class="cq-input @error('duration') is-invalid @enderror"
                        id="duration" name="duration" type="number"
                        min="1" max="300"
                        value="{{ old('duration', 30) }}"
                        required
                    />
                    @error('duration')
                        <div class="cq-error-msg">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ============================================================
             QUESTION TABS
             ============================================================ --}}
        <div class="cq-tabs-bar" id="cq-tabs-bar">
            {{-- Tabs injected by JS --}}
            <button type="button" class="cq-tab-add" id="cq-add-q-btn">＋ Tambah Soal</button>
        </div>

        {{-- ============================================================
             QUESTION PANELS
             ============================================================ --}}
        <div id="cq-panels">
            @foreach ($oldQuestions as $index => $question)
                @include('quizzes._question_panel', [
                    'index'    => $index,
                    'question' => $question,
                    'isActive' => $index === 0,
                ])
            @endforeach
        </div>

        {{-- ============================================================
             FOOTER ACTIONS
             ============================================================ --}}
        <div class="cq-footer-actions">
            <div class="cq-footer-left">
                <a href="{{ route('materials.show', [$subject, $material]) }}" class="cq-btn-ghost">
                    ← Batal
                </a>
            </div>
            <div class="cq-footer-right">
                <button type="button" class="cq-btn-preview" id="preview-btn">
                    <span id="preview-btn-icon">👁️</span>
                    <span id="preview-btn-text">Preview Soal Ini</span>
                </button>
                <button type="submit" class="cq-btn-save">
                    💾 Simpan Kuis
                </button>
            </div>
        </div>

    </form>{{-- end form --}}
</div>{{-- end cq-wrap --}}

{{-- ============================================================
     TEMPLATE FOR NEW QUESTION (cloned by JS)
     ============================================================ --}}
<template id="question-template">
    {{-- Will be filled by JS with __INDEX__ replaced --}}
</template>
@endsection

@push('scripts')
<script>
/* ================================================================
   CONSTANTS
   ================================================================ */
const LETTERS    = ['A', 'B', 'C', 'D'];
const OPT_KEYS   = ['a', 'b', 'c', 'd'];

/* ================================================================
   STATE
   ================================================================ */
let activeQIdx   = 0;
let totalQ       = document.querySelectorAll('.cq-qpanel').length;
let previewOpen  = false;

/* ================================================================
   INIT
   ================================================================ */
(function init() {
    renderTabs();
    updateQCountBadge();
    bindOptionRows();
    document.getElementById('cq-add-q-btn').addEventListener('click', addQuestion);
    document.getElementById('preview-btn').addEventListener('click', togglePreview);
})();

/* ================================================================
   TABS
   ================================================================ */
function renderTabs() {
    const bar    = document.getElementById('cq-tabs-bar');
    const addBtn = document.getElementById('cq-add-q-btn');
    bar.querySelectorAll('.cq-tab').forEach(t => t.remove());

    for (let i = 0; i < totalQ; i++) {
        const tab = document.createElement('button');
        tab.type      = 'button';
        tab.className = 'cq-tab' + (i === activeQIdx ? ' active' : '');
        tab.dataset.idx = i;
        tab.innerHTML = `Soal ${i + 1}${totalQ > 1
            ? `<span class="cq-tab-del" data-del="${i}">✕</span>`
            : ''}`;
        tab.addEventListener('click', (e) => {
            const del = e.target.closest('[data-del]');
            if (del) { removeQuestion(parseInt(del.dataset.del)); return; }
            switchTab(i);
        });
        bar.insertBefore(tab, addBtn);
    }
}

function switchTab(idx) {
    activeQIdx = idx;
    document.querySelectorAll('.cq-qpanel').forEach((p, i) => {
        p.classList.toggle('active', i === idx);
    });
    renderTabs();
    // sync preview button label
    if (previewOpen) {
        renderPreview(idx);
    }
}

function updateQCountBadge() {
    const b = document.getElementById('qcount-badge');
    if (b) b.textContent = totalQ + ' Soal';
}

/* ================================================================
   ADD QUESTION
   ================================================================ */
function addQuestion() {
    const idx     = totalQ;
    const panels  = document.getElementById('cq-panels');
    panels.insertAdjacentHTML('beforeend', buildPanelHTML(idx));
    totalQ++;
    activeQIdx = idx;
    renderTabs();
    updateQCountBadge();
    document.querySelectorAll('.cq-qpanel').forEach((p, i) => {
        p.classList.toggle('active', i === activeQIdx);
    });
    bindOptionRows(idx);
    showToast(`✅ Soal ${idx + 1} ditambahkan`, 'success');
}

/* ================================================================
   REMOVE QUESTION
   ================================================================ */
function removeQuestion(idx) {
    if (totalQ <= 1) { showToast('Minimal 1 soal.', 'error'); return; }
    if (!confirm(`Hapus Soal ${idx + 1}?`)) return;

    const panels = document.getElementById('cq-panels');
    const all    = panels.querySelectorAll('.cq-qpanel');
    all[idx].remove();
    totalQ--;

    // Re-index remaining panels + their inputs
    panels.querySelectorAll('.cq-qpanel').forEach((panel, i) => {
        panel.dataset.qidx = i;
        // Update all name attributes: questions[OLD] → questions[NEW]
        panel.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/questions\[\d+\]/, `questions[${i}]`);
        });
        // Update data-idx references
        panel.querySelectorAll('[data-qidx]').forEach(el => el.dataset.qidx = i);
    });

    activeQIdx = Math.min(activeQIdx, totalQ - 1);
    renderTabs();
    updateQCountBadge();
    document.querySelectorAll('.cq-qpanel').forEach((p, i) => {
        p.classList.toggle('active', i === activeQIdx);
    });
}

/* ================================================================
   BUILD PANEL HTML
   ================================================================ */
function buildPanelHTML(idx) {
    return `
<div class="cq-qpanel" data-qidx="${idx}">

  <!-- PREVIEW -->
  <div class="cq-preview-section" id="pv-section-${idx}">
    <div class="cq-preview-label">👁️ Preview Soal ${idx + 1}</div>
    <div class="cq-preview-card">
      <div class="cq-pv-num">${idx + 1}</div>
      <img class="cq-pv-img" id="pv-img-${idx}" src="" alt=""/>
      <div class="cq-pv-q" id="pv-q-${idx}">—</div>
      <div class="cq-pv-options" id="pv-opts-${idx}"></div>
      <div class="cq-pv-exp" id="pv-exp-${idx}" style="display:none">
        <div class="cq-pv-exp-label">💡 Pembahasan</div>
        <div class="cq-pv-exp-text" id="pv-exp-text-${idx}"></div>
      </div>
    </div>
  </div>

  <!-- PERTANYAAN + GAMBAR -->
  <div class="cq-card">
    <div class="cq-card-title">
      <div class="cq-card-title-icon">❓</div>
      Soal ${idx + 1}
    </div>

    <!-- Gambar: URL -->
    <div class="cq-field">
      <label class="cq-label">Link Gambar <span style="font-weight:400;color:var(--ink3)">(opsional)</span></label>
      <input class="cq-input" type="url"
        name="questions[${idx}][image_url]"
        placeholder="https://contoh.com/gambar.jpg"
        oninput="syncPreviewIfOpen(${idx})"/>
      <div class="cq-input-hint">Atau upload file di bawah — upload akan diprioritaskan.</div>
    </div>

    <!-- Gambar: Upload -->
    <div class="cq-field">
      <label class="cq-label">Upload Gambar <span style="font-weight:400;color:var(--ink3)">(opsional)</span></label>
      <div class="cq-img-preview-wrap" id="img-preview-wrap-${idx}">
        <img class="cq-img-preview" id="img-preview-${idx}" src="" alt="Preview"/>
        <button type="button" class="cq-img-remove" onclick="removeImage(${idx})">✕</button>
        <div class="cq-img-filename" id="img-filename-${idx}"></div>
      </div>
      <div class="cq-img-upload-area" id="img-upload-area-${idx}"
        ondragover="handleDragOver(event,${idx})"
        ondragleave="handleDragLeave(event,${idx})"
        ondrop="handleDrop(event,${idx})">
        <input class="cq-img-upload-input" type="file"
          name="questions[${idx}][image_file]"
          accept="image/*"
          onchange="handleImageUpload(event,${idx})"/>
        <div class="cq-img-upload-placeholder">
          <div class="cq-img-upload-icon">🖼️</div>
          <div class="cq-img-upload-text">Klik atau seret gambar ke sini</div>
          <div class="cq-img-upload-hint">JPG, PNG, GIF, WebP · Maks. 4 MB</div>
        </div>
      </div>
    </div>

    <!-- Teks Soal -->
    <div class="cq-field">
      <label class="cq-label">Teks Pertanyaan <span class="cq-label-req">*</span></label>
      <textarea class="cq-textarea" name="questions[${idx}][question]" rows="3"
        placeholder="Tulis pertanyaan di sini…" required
        oninput="syncPreviewIfOpen(${idx})"></textarea>
    </div>
  </div>

  <!-- PILIHAN JAWABAN -->
  <div class="cq-card">
    <div class="cq-card-title">
      <div class="cq-card-title-icon">🔤</div>
      Pilihan Jawaban
      <span style="font-size:.72rem;font-weight:600;color:var(--ink3);margin-left:auto">Radio = jawaban benar</span>
    </div>
    <div class="cq-options-list" id="opts-list-${idx}">
      ${LETTERS.map((letter, oi) => buildOptionRow(idx, oi, '', false)).join('')}
    </div>
    <!-- hidden correct_option -->
    <input type="hidden" name="questions[${idx}][correct_option]" id="correct-hidden-${idx}" value="a"/>
  </div>

  <!-- PEMBAHASAN -->
  <div class="cq-card">
    <div class="cq-card-title">
      <div class="cq-card-title-icon">💡</div>
      Pembahasan Jika Salah
    </div>
    <div class="cq-exp-wrap">
      <div class="cq-exp-label">💡 Pembahasan</div>
      <textarea class="cq-textarea" name="questions[${idx}][explanation]" rows="3"
        placeholder="Jelaskan konsep atau langkah jawaban yang benar (opsional)"
        oninput="syncPreviewIfOpen(${idx})"></textarea>
    </div>
  </div>

</div>`;
}

function buildOptionRow(idx, oi, value = '', isCorrect = false) {
    const letter = LETTERS[oi];
    const key    = OPT_KEYS[oi];
    return `
<div class="cq-option-row${isCorrect ? ' cq-correct-row' : ''}" id="opt-row-${idx}-${oi}">
  <div class="cq-option-letter${isCorrect ? '' : ''}" id="opt-letter-${idx}-${oi}">${letter}</div>
  <input class="cq-option-input" type="text"
    name="questions[${idx}][option_${key}]"
    placeholder="Pilihan ${letter}…"
    value="${escHtml(value)}"
    oninput="syncPreviewIfOpen(${idx})"
    required/>
  <label class="cq-correct-radio">
    <input type="radio" name="cq-radio-${idx}" value="${key}"
      ${isCorrect ? 'checked' : ''}
      onchange="setCorrect(${idx},'${key}',${oi})"/>
    Benar
  </label>
</div>`;
}

/* ================================================================
   BIND OPTION ROWS (for server-rendered panels from old())
   ================================================================ */
function bindOptionRows(specificIdx) {
    const panels = document.querySelectorAll('.cq-qpanel');
    panels.forEach((panel, i) => {
        if (specificIdx !== undefined && i !== specificIdx) return;
        panel.querySelectorAll('input[type=radio][name^="cq-radio-"]').forEach(radio => {
            radio.addEventListener('change', () => {
                const key = radio.value; // 'a','b','c','d'
                const oi  = OPT_KEYS.indexOf(key);
                setCorrect(i, key, oi);
            });
        });
        // Highlight server-rendered correct row on load
        const checkedRadio = panel.querySelector('input[type=radio]:checked');
        if (checkedRadio) {
            const key = checkedRadio.value;
            const oi  = OPT_KEYS.indexOf(key);
            setCorrect(i, key, oi);
        }
    });
}

/* ================================================================
   SET CORRECT
   ================================================================ */
function setCorrect(idx, key, oi) {
    // Update hidden input
    const hidden = document.getElementById(`correct-hidden-${idx}`);
    if (hidden) hidden.value = key;

    // Also keep a real select/hidden per Blade server-rendered panels
    const panel = document.querySelectorAll('.cq-qpanel')[idx];
    if (!panel) return;

    panel.querySelectorAll('.cq-option-row').forEach((row, i) => {
        row.classList.toggle('cq-correct-row', i === oi);
        const letter = row.querySelector('[id^="opt-letter-"]');
        // letter style handled by CSS .cq-correct-row .cq-option-letter
    });

    syncPreviewIfOpen(idx);
}

/* ================================================================
   IMAGE UPLOAD
   ================================================================ */
function handleImageUpload(e, idx) {
    const file = e.target.files[0];
    if (file) processImageFile(file, idx);
}
function handleDragOver(e, idx)  { e.preventDefault(); document.getElementById(`img-upload-area-${idx}`)?.classList.add('drag-over'); }
function handleDragLeave(e, idx) { document.getElementById(`img-upload-area-${idx}`)?.classList.remove('drag-over'); }
function handleDrop(e, idx) {
    e.preventDefault();
    document.getElementById(`img-upload-area-${idx}`)?.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) processImageFile(file, idx);
    else showToast('File harus berupa gambar.', 'error');
}
function processImageFile(file, idx) {
    if (file.size > 4 * 1024 * 1024) { showToast('Ukuran gambar maks. 4 MB.', 'error'); return; }
    const reader = new FileReader();
    reader.onload = (ev) => {
        const wrap = document.getElementById(`img-preview-wrap-${idx}`);
        const img  = document.getElementById(`img-preview-${idx}`);
        const area = document.getElementById(`img-upload-area-${idx}`);
        const fn   = document.getElementById(`img-filename-${idx}`);
        if (!wrap || !img) return;
        img.src = ev.target.result;
        if (fn) fn.textContent = file.name;
        wrap.classList.add('has-img');
        if (area) area.style.display = 'none';
        syncPreviewIfOpen(idx);
    };
    reader.readAsDataURL(file);
}
function removeImage(idx) {
    const wrap = document.getElementById(`img-preview-wrap-${idx}`);
    const img  = document.getElementById(`img-preview-${idx}`);
    const area = document.getElementById(`img-upload-area-${idx}`);
    if (img)  img.src = '';
    if (wrap) wrap.classList.remove('has-img');
    if (area) area.style.display = '';
    // Clear file input
    const panel    = document.querySelectorAll('.cq-qpanel')[idx];
    const fileInput = panel?.querySelector('input[type=file]');
    if (fileInput) fileInput.value = '';
    syncPreviewIfOpen(idx);
}

/* ================================================================
   PREVIEW
   ================================================================ */
function togglePreview() {
    previewOpen = !previewOpen;
    document.getElementById('preview-btn-icon').textContent = previewOpen ? '🙈' : '👁️';
    document.getElementById('preview-btn-text').textContent = previewOpen ? 'Sembunyikan' : 'Preview Soal Ini';

    const section = document.getElementById(`pv-section-${activeQIdx}`);
    if (section) {
        section.classList.toggle('visible', previewOpen);
        if (previewOpen) {
            renderPreview(activeQIdx);
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

function syncPreviewIfOpen(idx) {
    if (previewOpen && idx === activeQIdx) renderPreview(idx);
}

function renderPreview(idx) {
    const panel  = document.querySelectorAll('.cq-qpanel')[idx];
    if (!panel) return;
    const qTxt   = panel.querySelector(`[name="questions[${idx}][question]"]`)?.value.trim() || '(belum diisi)';
    const exp    = panel.querySelector(`[name="questions[${idx}][explanation]"]`)?.value.trim() || '';
    const imgPreview = document.getElementById(`img-preview-${idx}`);
    const imgURL     = panel.querySelector(`[name="questions[${idx}][image_url]"]`)?.value.trim() || '';

    document.getElementById(`pv-q-${idx}`).textContent = qTxt;

    // Image
    const pvImg = document.getElementById(`pv-img-${idx}`);
    if (pvImg) {
        const src = (imgPreview?.src && imgPreview.src !== window.location.href) ? imgPreview.src : (imgURL || '');
        pvImg.src = src;
        pvImg.classList.toggle('has-img', !!src);
    }

    // Options
    const optsEl = document.getElementById(`pv-opts-${idx}`);
    if (optsEl) {
        optsEl.innerHTML = '';
        OPT_KEYS.forEach((key, oi) => {
            const inputEl  = panel.querySelector(`[name="questions[${idx}][option_${key}]"]`);
            const text     = inputEl?.value.trim() || '(kosong)';
            const radioEl  = panel.querySelector(`input[type=radio][value="${key}"]`);
            const isCorrect = radioEl?.checked || false;
            const div = document.createElement('div');
            div.className = 'cq-pv-opt' + (isCorrect ? ' cq-pv-correct' : '');
            div.innerHTML = `<span class="cq-pv-opt-letter">${LETTERS[oi]}</span>${escHtml(text)}${isCorrect ? ' ✅' : ''}`;
            optsEl.appendChild(div);
        });
    }

    // Explanation
    const expEl = document.getElementById(`pv-exp-${idx}`);
    if (expEl) {
        document.getElementById(`pv-exp-text-${idx}`).textContent = exp;
        expEl.style.display = exp ? 'block' : 'none';
    }
}

/* ================================================================
   HELPERS
   ================================================================ */
function escHtml(s) {
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(msg, type = '') {
    document.querySelector('.cq-toast')?.remove();
    const t = document.createElement('div');
    t.className = 'cq-toast' + (type ? ' cq-toast-' + type : '');
    t.textContent = msg;
    document.body.appendChild(t);
    t.addEventListener('animationend', () => t.remove());
}
</script>
@endpush