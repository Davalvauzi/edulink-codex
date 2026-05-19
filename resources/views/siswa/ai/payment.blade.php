@extends('layouts.portal')

@section('heading', 'Pembayaran AI Tutor')
@section('subtitle', 'Bayar paket konsultasi AI khusus siswa untuk membahas materi dan kuis.')

@section('content')
    <section class="cards">
        <article class="card">
            <strong>Bayar dengan QR</strong>
            <p>Scan QR di bawah untuk melakukan pembayaran offline. Setelah pembayaran selesai, tekan tombol konfirmasi agar akses AI Anda dibuka.</p>
            <div style="text-align:center; margin: 2rem 0;">
                <img src="{{ $qrImageUrl }}" alt="{{ $qrImageAlt }}" style="max-width:320px; width:100%; height:auto;" />
            </div>
            <p>{{ config('ai.payment_instructions') }}</p>
        </article>
    </section>

    @if ($user->hasUnlimitedAiAccess())
        <div class="alert alert-success">
            Akses AI Anda sudah aktif dan tidak terbatas.
        </div>
    @elseif ($isRequested)
        <div class="alert alert-info">
            Permintaan konfirmasi pembayaran telah dikirim. Tunggu admin untuk menyetujui akses AI Anda.
        </div>
    @else
        <form method="POST" action="{{ route('siswa.ai.payment.confirm') }}">
            @csrf
            <div class="field field-full">
                <button class="btn btn-primary" type="submit">Saya Sudah Bayar, Konfirmasi Akses</button>
            </div>
        </form>
    @endif
@endsection
