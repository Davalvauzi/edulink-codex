@extends('layouts.portal')

@section('heading', 'Pembayaran AI Tutor')
@section('subtitle', 'Bayar paket konsultasi AI khusus siswa untuk membahas materi dan kuis.')

@section('actions')
    <a class="btn btn-soft" href="{{ route('siswa.ai.index') }}">Kembali ke AI</a>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    <section class="meta stack">
        <article class="card">
            <strong>{{ $packages['ai_tutor']['label'] }}</strong>
            <p>{{ $packages['ai_tutor']['description'] }}</p>
            <p class="price">Rp {{ number_format($packages['ai_tutor']['amount'], 0, ',', '.') }}</p>
            <form method="POST" action="{{ route('siswa.payments.store') }}">
                @csrf
                <input type="hidden" name="package" value="ai_tutor">
                <button class="btn btn-primary" type="submit">Bayar Sekarang</button>
            </form>
        </article>
    </section>

    @if($recentPayments->isNotEmpty())
        <section class="meta stack">
            <div class="section-title">
                <div>
                    <strong>Riwayat Pembayaran</strong>
                    <p>Transaksi terakhir Anda akan ditampilkan di sini.</p>
                </div>
            </div>

            <div class="card-stack">
                @foreach($recentPayments as $payment)
                    <article class="card compact">
                        <strong>{{ $payment->package_label }}</strong>
                        <p>Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                        <p>Status: {{ ucfirst($payment->status) }}</p>
                        <a class="btn btn-soft" href="{{ route('siswa.payments.show', $payment) }}">Lihat</a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
