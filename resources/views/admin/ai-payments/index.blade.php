@extends('layouts.portal')

@section('title', 'Konfirmasi Pembayaran AI Tutor')
@section('heading', 'Konfirmasi Pembayaran AI Tutor')
@section('subtitle', 'Kelola permintaan akses AI dari siswa setelah mereka membayar melalui QR.')

@section('content')
    <section class="cards">
        <article class="card">
            <strong>Permintaan Akses AI Pending</strong>
            <p>Di bawah ini adalah siswa yang telah meminta konfirmasi akses AI. Setujui jika pembayaran sudah diterima.</p>
        </article>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($pendingRequests->isEmpty())
        <div class="card">
            <p>Tidak ada permintaan AI yang menunggu saat ini.</p>
        </div>
    @else
        <div class="card">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Nama Pengirim</th>
                            <th>Waktu Permintaan</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingRequests as $pending)
                            <tr>
                                <td>{{ $pending->name }}</td>
                                <td>{{ $pending->email }}</td>
                                <td>{{ $pending->ai_tutor_payment_sender_name ?? '-' }}</td>
                                <td>{{ $pending->ai_tutor_payment_requested_at->translatedFormat('d F Y H:i') }}</td>
                                <td>
                                    <div class="table-actions">
                                        <form class="inline-form" method="POST"
                                            action="{{ route('admin.ai-payments.approve', ['user' => $pending->id]) }}">
                                            @csrf
                                            <button class="btn btn-primary" type="submit">Setujui</button>
                                        </form>

                                        <form class="inline-form" method="POST"
                                            action="{{ route('admin.ai-payments.deny', ['user' => $pending->id]) }}">
                                            @csrf
                                            <button class="btn btn-secondary" type="submit">Tolak</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
