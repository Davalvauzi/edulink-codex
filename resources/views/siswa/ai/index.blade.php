@extends('layouts.portal')

@section('heading', 'Tingkatkan AI Kamu')
@section('subtitle', 'Akses unlimited ke AI Tutor untuk pembelajaran yang lebih efektif')

@section('content')
    @if ($user->hasUnlimitedAiAccess())
        <div class="alert alert-success">
            Selamat! Akses AI Unlimited Anda sudah aktif. Nikmati konsultasi AI tanpa batas.
        </div>
    @elseif ($user->hasRequestedAiPayment())
        <div class="alert alert-info">
            Permintaan upgrade Anda sedang ditinjau admin. Anda akan menerima notifikasi segera setelah disetujui.
        </div>
    @endif

    <section class="cards">
        <article class="card">
            <strong>✨ Fitur Premium</strong>
            <p><strong>Tanya AI Unlimited</strong> memberikan akses tanpa batas ke AI Tutor untuk membahas materi, menjawab pertanyaan, dan melatih konsep pelajaran apapun.</p>
        </article>
        <article class="card">
            <strong>🎯 Untuk Akun Gratis</strong>
            <p>Setiap hari Anda mendapatkan <strong>7 pertanyaan</strong> untuk bertanya kepada AI Tutor. Reset otomatis setiap tengah malam.</p>
        </article>
        <article class="card">
            <strong>💎 Untuk Langganan Premium</strong>
            <p><strong>Pertanyaan unlimited</strong> tanpa batasan harian. Tanya AI kapan saja dan berapa kali saja sepanjang tahun.</p>
        </article>
    </section>

    <section class="cards">
        <article class="card" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong>📊 Riwayat Pesan Anda</strong>
                @if ($user->hasUnlimitedAiAccess())
                    <p>Akses unlimited aktif. Gunakan AI Tutor tanpa batasan.</p>
                @else
                    <p>Sisa <strong>{{ $user->remainingAiChats() }} pertanyaan</strong> tersedia hari ini dari <strong>7 pertanyaan harian</strong>.</p>
                    <p style="margin-top: 12px; font-size: 13px; color: #5a6f69;">Gunakan dengan bijak dan reset akan terjadi besok pukul 00:00 WIB.</p>
                @endif
            </div>
            @if (!$user->hasUnlimitedAiAccess())
                <a class="btn btn-primary" href="{{ route('siswa.ai.payment') }}" style="white-space: nowrap; margin-left: 20px;">
                    Upgrade Sekarang
                </a>
            @endif
        </article>
    </section>

    <section class="cards">
        <article class="card">
            <strong>🚀 Manfaat Lainnya</strong>
            <p>✓ Respons cepat dari AI Tutor<br>
            ✓ Cocok untuk mempersiapkan ujian<br>
            ✓ Bantu memahami konsep sulit<br>
            ✓ Latihan soal dan pembahasan<br>
            ✓ Akses kapan saja, di mana saja</p>
        </article>
    </section>

    @if (!$user->hasUnlimitedAiAccess() && !$user->hasRequestedAiPayment())
        <div class="meta" style="text-align: center;">
            <p style="margin: 0 0 16px; color: #5a6f69;">Siap untuk upgrade? Klik tombol di atas atau ikuti proses pembayaran lengkap di halaman upgrade.</p>
            <a class="btn btn-primary" href="{{ route('siswa.ai.payment') }}">Lihat Detail Pembayaran & Upgrade</a>
        </div>
    @endif
@endsection

