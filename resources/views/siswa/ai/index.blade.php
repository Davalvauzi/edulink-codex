@extends('layouts.portal')

@section('sidebar')
    <div class="static-item">
        Siswa Login
        <span>{{ $user->name }}</span>
    </div>
    <div class="static-item">
        Email
        <span>{{ $user->email }}</span>
    </div>
    <div class="static-item">
        Status AI
        <span>{{ $user->hasUnlimitedAiAccess() ? 'Unlimited aktif' : ($user->hasRequestedAiPayment() ? 'Menunggu admin' : 'Akun gratis') }}</span>
    </div>
@endsection

@section('heading', 'Tingkatkan AI Kamu')
@section('subtitle', 'Kelola akses AI Tutor, cek kuota harian, dan upgrade saat membutuhkan sesi belajar tanpa batas.')

@push('styles')
<style>
.ai-compact .meta{margin-top:14px;padding:16px;border-radius:14px}
.ai-compact .cards{grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:14px}
.ai-compact .card{padding:16px;border-radius:14px}
.ai-compact .ai-hero{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(220px,.8fr);gap:16px;align-items:center}
.ai-compact .ai-hero h2{font-size:1.2rem;line-height:1.25;margin:0 0 8px}
.ai-compact .ai-hero p,.ai-compact .feature-list{font-size:.86rem}
.ai-compact .ai-quota-value{font-size:1.6rem;margin:8px 0 4px}
@media(max-width:900px){.ai-compact .ai-hero,.ai-compact .cards{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="ai-compact">
    @if ($user->hasUnlimitedAiAccess())
        <div class="alert success">
            Selamat! Akses AI Unlimited Anda sudah aktif. Nikmati konsultasi AI tanpa batas.
        </div>
    @elseif ($user->hasRequestedAiPayment())
        <div class="alert info">
            Permintaan upgrade Anda sedang ditinjau admin. Anda akan menerima notifikasi segera setelah disetujui.
        </div>
    @endif

    <section class="meta ai-hero">
        <div class="ai-hero-copy">
            <span class="subject-badge">AI Tutor Siswa</span>
            <h2>Belajar lebih bebas dengan bantuan AI yang paham konteks materi dan kuis.</h2>
            <p>AI Tutor bisa membantu menjelaskan materi, merangkum bagian yang sulit, dan membahas jawaban kuis yang kurang tepat dengan bahasa yang lebih bertahap.</p>
            <div class="subsection-actions">
                @if (! $user->hasUnlimitedAiAccess())
                    <a class="btn btn-primary" href="{{ route('siswa.ai.payment') }}">Upgrade Sekarang</a>
                @endif
                <a class="btn btn-soft" href="{{ route('siswa.dashboard') }}">Kembali ke Dashboard</a>
            </div>
        </div>

        <aside class="ai-status-card">
            <strong>Status Akun</strong>
            @if ($user->hasUnlimitedAiAccess())
                <h3 class="ai-quota-value">Unlimited</h3>
                <p>Akses AI Anda sudah aktif tanpa batas pertanyaan harian.</p>
            @else
                <h3 class="ai-quota-value">{{ $user->remainingAiChats() }}</h3>
                <p>pertanyaan gratis tersisa hari ini dari 7 pertanyaan harian.</p>
                <div class="progress-track compact">
                    <div class="progress-fill" style="width: {{ ($user->remainingAiChats() / 7) * 100 }}%;"></div>
                </div>
                <span class="progress-value">Reset kuota setiap hari</span>
            @endif
        </aside>
    </section>

    <section class="cards">
        <article class="card">
            <strong>Akun Gratis</strong>
            <p>Gunakan 7 pertanyaan harian untuk bertanya tentang materi atau hasil kuis.</p>
        </article>
        <article class="card">
            <strong>Akses Unlimited</strong>
            <p>Tanya AI kapan saja tanpa batasan harian setelah pembayaran disetujui admin.</p>
        </article>
        <article class="card">
            <strong>Belajar Berbasis Kuis</strong>
            <p>AI dapat memakai hasil kuis terbaru untuk membantu membahas konsep yang masih belum pas.</p>
        </article>
    </section>

    <section class="meta">
        <div class="section-title">
            <div>
                <strong>Manfaat AI Tutor</strong>
                <p>Fitur ini dirancang untuk membantu proses belajar harian tanpa menggantikan guru.</p>
            </div>
        </div>
        <ul class="feature-list">
            <li>Respons cepat untuk pertanyaan tentang materi dan latihan soal.</li>
            <li>Pembahasan jawaban salah dengan penjelasan yang lebih pelan dan terarah.</li>
            <li>Cocok untuk mengulang konsep sebelum kuis atau ujian.</li>
            <li>Bisa digunakan dari konteks materi maupun hasil kuis.</li>
        </ul>
    </section>

    @if (! $user->hasUnlimitedAiAccess() && ! $user->hasRequestedAiPayment())
        <div class="meta stack">
            <strong>Siap Upgrade?</strong>
            <p class="muted-note">Lanjut ke halaman pembayaran untuk melihat QR dan mengirim nama pengirim transfer.</p>
            <a class="btn btn-primary" href="{{ route('siswa.ai.payment') }}">Lihat Detail Pembayaran & Upgrade</a>
        </div>
    @endif
</div>
@endsection
