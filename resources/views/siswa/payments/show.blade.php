@extends('layouts.portal')

@section('heading', 'Status Pembayaran')
@section('subtitle', 'Periksa status pembayaran dan lanjutkan proses jika diperlukan.')

@section('actions')
    <a class="btn btn-soft" href="{{ route('siswa.payments.create') }}">Kembali ke Pembayaran</a>
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
            <div class="field-row">
                <div>
                    <strong>Order ID</strong>
                    <p>{{ $payment->order_id }}</p>
                </div>
                <div>
                    <strong>Status</strong>
                    <p>{{ ucfirst($payment->status) }}</p>
                </div>
            </div>
            <div class="field-row">
                <div>
                    <strong>Paket</strong>
                    <p>{{ $payment->package_label }}</p>
                </div>
                <div>
                    <strong>Jumlah</strong>
                    <p>Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="field-row">
                <div>
                    <strong>Metode</strong>
                    <p>{{ $payment->payment_type ?? 'Belum dipilih' }}</p>
                </div>
                <div>
                    <strong>Dibayar pada</strong>
                    <p>{{ $payment->paid_at ? $payment->paid_at->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </article>

        <div class="subsection-actions">
            @if($payment->payment_url && ! $payment->isPaid())
                <a class="btn btn-primary" href="{{ $payment->payment_url }}" target="_blank">Lanjutkan Pembayaran</a>
            @endif
            <a class="btn btn-soft" href="{{ route('siswa.payments.refresh', $payment) }}">Segarkan Status</a>
        </div>
    </section>
@endsection
