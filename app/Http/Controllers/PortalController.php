<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\LevelEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    private function member(): Member
    {
        return auth()->user()->member()->with('hotel', 'level', 'activeCard')->firstOrFail();
    }

    public function dashboard(LevelEngine $engine): View
    {
        $member = $this->member();
        $progress = $engine->progressToNextLevel($member);

        return view('portal.dashboard', [
            'member' => $member,
            'progress' => $progress,
            'recentVisits' => $member->visits()->with('hotel')->latest('visit_date')->limit(5)->get(),
            'availableVouchers' => $member->vouchers()->where('status', \App\Models\Voucher::STATUS_AVAILABLE)->count(),
        ]);
    }

    public function card(): View
    {
        $member = $this->member();

        return view('portal.card', ['member' => $member]);
    }

    public function visits(Request $request): View
    {
        $member = $this->member();
        $visits = $member->visits()->with('hotel', 'transaction', 'staff')
            ->orderByDesc('visit_date')->paginate(15);

        return view('portal.visits', ['member' => $member, 'visits' => $visits]);
    }

    public function transactions(Request $request): View
    {
        $member = $this->member();
        $transactions = $member->transactions()->with('hotel')
            ->orderByDesc('transaction_date')->paginate(15);

        return view('portal.transactions', ['member' => $member, 'transactions' => $transactions]);
    }

    public function benefits(): View
    {
        $member = $this->member();

        $levelBenefits = \App\Models\MembershipBenefit::where('is_active', true)
            ->where(function ($q) use ($member) {
                $q->whereNull('level_id')->orWhere('level_id', $member->level_id);
            })
            ->where(function ($q) use ($member) {
                $q->whereNull('hotel_id')->orWhere('hotel_id', $member->hotel_id);
            })
            ->get();

        return view('portal.benefits', [
            'member' => $member,
            'levelBenefits' => $levelBenefits,
            'vouchers' => $member->benefits()->with('benefit')->latest()->get(),
        ]);
    }

    /** CPC v2.0 §5–§6 — daftar voucer member + riwayat penukaran. */
    public function vouchers(Request $request): View
    {
        $member = $this->member();

        $status = strtoupper((string) $request->input('status', ''));
        $validStatuses = [\App\Models\Voucher::STATUS_AVAILABLE, \App\Models\Voucher::STATUS_PENDING_APPROVAL, \App\Models\Voucher::STATUS_REDEEMED, \App\Models\Voucher::STATUS_EXPIRED, \App\Models\Voucher::STATUS_CANCELLED];
        if (! in_array($status, $validStatuses, true)) {
            $status = '';
        }

        $vouchers = $member->vouchers()->with(['type', 'period', 'hotel'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderByDesc('issued_at')
            ->orderBy('voucher_no')
            ->paginate(20)
            ->withQueryString();

        $counts = $member->vouchers()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $requests = \App\Models\RedemptionRequest::where('member_id', $member->id)
            ->with(['voucher.type', 'requester', 'decider', 'hotel'])
            ->latest()
            ->limit(15)
            ->get();

        return view('portal.vouchers', [
            'member' => $member,
            'vouchers' => $vouchers,
            'counts' => $counts,
            'requests' => $requests,
            'status' => $status,
        ]);
    }

    public function profile(): View
    {
        return view('portal.profile', ['member' => $this->member()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $member = $this->member();

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'company' => ['nullable', 'string', 'max:150'],
            'occupation' => ['nullable', 'string', 'max:150'],
        ]);

        $member->update($validated);
        \App\Models\AuditLog::record('member_profile_updated', 'Member', $member->id, 'Update profil oleh member');

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
