<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private MembershipService $membership) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $hotelScope = $user->hasGlobalAccess() ? null : $user->hotel_id;

        $query = Payment::query()->with(['member.hotel', 'member.level', 'invoice', 'verifier'])
            ->when($hotelScope, fn ($q) => $q->whereHas('member', fn ($m) => $m->where('hotel_id', $hotelScope)));

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($q = trim((string) $request->query('q'))) {
            $query->whereHas('member', fn ($m) => $m
                ->where('member_no', 'like', "%{$q}%")
                ->orWhere('full_name', 'like', "%{$q}%"));
        }

        $payments = $query->latest()->paginate(15)->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'statuses' => Payment::STATUSES,
            'filters' => $request->only(['status', 'q']),
        ]);
    }

    public function markSubmitted(Payment $payment): RedirectResponse
    {
        if (! in_array($payment->status, ['pending'])) {
            return back()->with('error', 'Status payment tidak bisa diubah.');
        }

        $payment->update(['status' => 'payment_submitted']);
        AuditLog::record('payment_submitted', 'Payment', $payment->id, 'Payment ditandai sudah disubmit member');

        return back()->with('success', 'Payment ditandai: Payment Submitted.');
    }

    public function verify(Payment $payment): RedirectResponse
    {
        if (! in_array($payment->status, ['pending', 'payment_submitted'])) {
            return back()->with('error', 'Payment sudah diproses.');
        }

        $payment->update([
            'status' => 'paid',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'paid_at' => now(),
        ]);

        $payment->invoice?->update(['status' => 'paid']);

        $member = $payment->member;
        if ($member->status === 'pending_payment') {
            $this->membership->activate($member);
            app(\App\Services\EmailService::class)->sendPaymentVerified($member, $payment);
        } elseif ($member->status === 'active' && $member->isPaid()) {
            // CPC §15 — perpanjangan Diamond: payment baru → periode baru + paket voucer baru
            $this->membership->renewDiamond($member, $payment);
            app(\App\Services\EmailService::class)->sendPaymentVerified($member, $payment);
        }

        AuditLog::record('payment_verified', 'Payment', $payment->id,
            'Payment Rp' . number_format($payment->amount, 0, ',', '.') . ' diverifikasi');

        return back()->with('success', 'Payment terverifikasi — membership diaktifkan.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate(['notes' => ['nullable', 'string', 'max:500']]);

        if (in_array($payment->status, ['paid', 'refunded'])) {
            return back()->with('error', 'Payment sudah final.');
        }

        $payment->update([
            'status' => 'failed',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'notes' => $validated['notes'] ?? $payment->notes,
        ]);

        AuditLog::record('payment_rejected', 'Payment', $payment->id, 'Payment ditolak: ' . ($validated['notes'] ?? '-'));

        return back()->with('success', 'Payment ditandai gagal.');
    }
}
