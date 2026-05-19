<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    private const PACKAGES = [
        'ai_tutor' => [
            'label' => 'Paket AI Tutor',
            'description' => 'Akses konsultasi AI untuk membahas materi dan soal kuis.',
            'amount' => 1,
        ],
    ];

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('siswa.payments.create', [
            'title' => 'Pembayaran',
            'role' => $user->role,
            'user' => $user,
            'packages' => self::PACKAGES,
            'recentPayments' => $user->payments()->latest()->take(5)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'package' => ['required', 'in:' . implode(',', array_keys(self::PACKAGES))],
        ]);

        $package = self::PACKAGES[$validated['package']];
        $orderId = 'EDU-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));

        $payment = Payment::query()->create([
            'user_id' => $user->id,
            'order_id' => $orderId,
            'package' => $validated['package'],
            'package_label' => $package['label'],
            'amount' => $package['amount'],
            'currency' => 'IDR',
            'status' => 'pending',
        ]);

        try {
            $response = Http::withBasicAuth(config('services.midtrans.server_key'), '')
                ->acceptJson()
                ->post($this->snapEndpoint(), [
                    'transaction_details' => [
                        'order_id' => $payment->order_id,
                        'gross_amount' => $payment->amount,
                    ],
                    'item_details' => [
                        [
                            'id' => $payment->package,
                            'price' => $payment->amount,
                            'quantity' => 1,
                            'name' => $payment->package_label,
                        ],
                    ],
                    'customer_details' => [
                        'first_name' => $user->name,
                        'email' => $user->email,
                    ],
                    'enabled_payments' => config('services.midtrans.enabled_payments', ['gopay', 'bank_transfer']),
                ]);

            if ($response->failed()) {
                $responseData = $response->json();
                $errorMessage = $responseData['error_messages'][0] ?? $response->body();

                Log::warning('Midtrans checkout failed response', [
                    'order_id' => $payment->order_id,
                    'status' => $response->status(),
                    'response' => $responseData,
                ]);

                $payment->update([
                    'status' => 'failed',
                    'response_data' => $responseData,
                ]);

                return redirect()
                    ->route('siswa.payments.create')
                    ->with('error', 'Gagal membuat transaksi pembayaran: ' . $errorMessage);
            }

            $payment->update([
                'payment_url' => $response->json('redirect_url'),
                'response_data' => $response->json(),
            ]);

            if ($redirectUrl = $response->json('redirect_url')) {
                return redirect()->away($redirectUrl);
            }

            return redirect()
                ->route('siswa.payments.show', $payment)
                ->with('success', 'Transaksi dibuat. Silakan lanjutkan ke halaman pembayaran.');
        } catch (RequestException $exception) {
            Log::error('Midtrans checkout failed', ['error' => $exception->getMessage(), 'order_id' => $payment->order_id]);

            $payment->update([
                'status' => 'failed',
                'response_data' => ['error' => $exception->getMessage()],
            ]);

            return redirect()
                ->route('siswa.payments.create')
                ->with('error', 'Terjadi kesalahan saat menghubungkan ke provider pembayaran.');
        }
    }

    public function show(Request $request, Payment $payment): View
    {
        abort_if($request->user()->id !== $payment->user_id, 403);

        return view('siswa.payments.show', [
            'title' => 'Status Pembayaran',
            'role' => $request->user()->role,
            'user' => $request->user(),
            'payment' => $payment,
        ]);
    }

    public function refresh(Request $request, Payment $payment): RedirectResponse
    {
        abort_if($request->user()->id !== $payment->user_id, 403);

        $this->refreshPaymentStatus($payment);

        return redirect()
            ->route('siswa.payments.show', $payment)
            ->with('success', 'Status pembayaran diperbarui.');
    }

    public function notify(Request $request): Response
    {
        $payload = $request->all();

        if (! $this->verifyMidtransSignature($payload)) {
            return response('Invalid signature', 422);
        }

        $payment = Payment::query()->where('order_id', $payload['order_id'])->first();

        if (! $payment) {
            return response('Payment not found', 404);
        }

        $payment->update([
            'status' => $payload['transaction_status'] ?? $payment->status,
            'payment_type' => $payload['payment_type'] ?? $payment->payment_type,
            'paid_at' => in_array($payload['transaction_status'] ?? '', ['capture', 'settlement'], true) ? now() : null,
            'response_data' => $payload,
        ]);

        return response('OK', 200);
    }

    private function refreshPaymentStatus(Payment $payment): void
    {
        $response = Http::withBasicAuth(config('services.midtrans.server_key'), '')
            ->acceptJson()
            ->get($this->statusEndpoint($payment->order_id));

        if ($response->successful()) {
            $status = $response->json('transaction_status');

            $payment->update([
                'status' => $status,
                'payment_type' => $response->json('payment_type', $payment->payment_type),
                'paid_at' => in_array($status, ['capture', 'settlement'], true) ? now() : null,
                'response_data' => $response->json(),
            ]);
        }
    }

    private function verifyMidtransSignature(array $payload): bool
    {
        if (! isset($payload['order_id'], $payload['status_code'], $payload['gross_amount'], $payload['signature_key'])) {
            return false;
        }

        $expected = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . config('services.midtrans.server_key'));

        return hash_equals($expected, $payload['signature_key']);
    }

    private function snapEndpoint(): string
    {
        return config('services.midtrans.env') === 'production'
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    private function statusEndpoint(string $orderId): string
    {
        return config('services.midtrans.env') === 'production'
            ? sprintf('https://api.midtrans.com/v2/%s/status', $orderId)
            : sprintf('https://api.sandbox.midtrans.com/v2/%s/status', $orderId);
    }
}
