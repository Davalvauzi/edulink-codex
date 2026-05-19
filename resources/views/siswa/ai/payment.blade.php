@extends('layouts.portal')

@section('sidebar')
    <a href="{{ route('siswa.ai.index') }}">
        Kembali ke AI Tutor
        <span>Tingkatkan AI Kamu</span>
    </a>
    <div class="static-item">
        Siswa Login
        <span>{{ $user->name }}</span>
    </div>
    <div class="static-item">
        Email
        <span>{{ $user->email }}</span>
    </div>
    <div class="static-item">
        Status Pembayaran
        <span>{{ $user->hasUnlimitedAiAccess() ? 'Akses aktif' : ($isRequested ? 'Menunggu admin' : 'Belum konfirmasi') }}</span>
    </div>
@endsection

@section('heading', 'Pembayaran AI Tutor')
@section('subtitle', 'Scan QR, isi nama pengirim, lalu kirim permintaan konfirmasi agar admin bisa membuka akses AI.')

@section('actions')
    <a class="btn btn-soft" href="{{ route('siswa.ai.index') }}">Kembali</a>
@endsection

@push('styles')
<style>
.payment-compact .meta{margin-top:14px;padding:16px;border-radius:14px}
.payment-compact .payment-grid{display:grid;grid-template-columns:minmax(260px,.9fr) minmax(0,1.1fr);gap:16px;align-items:start}
.payment-compact .qr-panel,.payment-compact .payment-copy{min-width:0}
.payment-compact .qr-frame{display:flex;align-items:center;justify-content:center;max-height:calc(100vh - 290px);min-height:220px;overflow:hidden}
.payment-compact .qr-frame img{max-width:100%;max-height:calc(100vh - 320px);object-fit:contain;display:block}
.payment-compact .info-strip{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:12px}
.payment-compact .explanation-card{padding:12px;border-radius:12px;border:1px solid var(--bd);background:#fff}
.payment-compact .material-form{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;align-items:end}
.payment-compact .material-form .field-full{width:auto;flex-basis:auto}
@media(max-width:900px){.payment-compact .payment-grid,.payment-compact .material-form{grid-template-columns:1fr}.payment-compact .qr-frame,.payment-compact .qr-frame img{max-height:55vh}}
</style>
@endpush

@section('content')
<div class="payment-compact">
    <section class="meta payment-grid">
        <article class="qr-panel">
            <span class="subject-badge">QR Pembayaran</span>
            <div class="qr-frame">
                <img src="{{ $qrImageUrl }}" alt="{{ $qrImageAlt }}">
            </div>
            <p class="muted-note">{{ $qrImageAlt }}</p>
        </article>

        <article class="payment-copy">
            <div>
                <strong>Bayar dengan QR</strong>
                <p>Scan QR di samping, selesaikan pembayaran, lalu kirim nama pengirim transfer dari form di bawah.</p>
            </div>
            <div class="explanation-card">
                <strong>Instruksi Pembayaran</strong>
                <p>{{ config('ai.payment_instructions') }}</p>
            </div>
            <div class="info-strip">
                <div class="mini-info">
                    <span>Akun</span>
                    <strong>{{ $user->name }}</strong>
                    <p>{{ $user->email }}</p>
                </div>
                <div class="mini-info">
                    <span>Status</span>
                    <strong>{{ $user->hasUnlimitedAiAccess() ? 'Aktif' : ($isRequested ? 'Diproses' : 'Belum bayar') }}</strong>
                    <p>Konfirmasi dilakukan oleh admin.</p>
                </div>
            </div>
        </article>
    </section>

    @if ($user->hasUnlimitedAiAccess())
        <div class="alert success">
            Akses AI Anda sudah aktif dan tidak terbatas.
        </div>
    @elseif ($isRequested)
        <div class="alert info">
            Permintaan konfirmasi pembayaran telah dikirim. Tunggu admin untuk menyetujui akses AI Anda.
        </div>
    @else
        <form class="meta material-form" method="POST" action="{{ route('siswa.ai.payment.confirm') }}">
            @csrf
            <div class="field field-full">
                <label for="payment_sender_name">Nama Pengirim</label>
                <input id="payment_sender_name" type="text" name="payment_sender_name" value="{{ old('payment_sender_name', $user->name) }}" placeholder="Nama yang melakukan transfer" required maxlength="100">
                @error('payment_sender_name')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="field field-full">
                <button class="btn btn-primary" type="submit">Saya Sudah Bayar, Konfirmasi Akses</button>
            </div>
        </form>
    @endif
</div>
@endsection
