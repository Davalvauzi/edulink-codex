<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAiPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_if($user->role !== 'admin', 403);

        $pendingRequests = User::query()
            ->whereNotNull('ai_tutor_payment_requested_at')
            ->whereNull('ai_tutor_paid_at')
            ->orderBy('ai_tutor_payment_requested_at', 'desc')
            ->get();

        return view('admin.ai-payments.index', [
            'role' => $user->role,
            'user' => $user,
            'pendingRequests' => $pendingRequests,
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->role !== 'admin', 403);

        $user->forceFill([
            'ai_tutor_paid_at' => now(),
            'ai_tutor_payment_requested_at' => null,
        ])->save();

        return redirect()
            ->route('admin.ai-payments.index')
            ->with('success', "Akses AI untuk siswa {$user->name} telah disetujui.");
    }

    public function deny(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->role !== 'admin', 403);

        $user->forceFill(['ai_tutor_payment_requested_at' => null])->save();

        return redirect()
            ->route('admin.ai-payments.index')
            ->with('success', "Permintaan akses AI untuk siswa {$user->name} telah ditolak.");
    }
}
