<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Services\MembershipService;
use App\Services\VoucherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(
        private MembershipService $membership,
        private VoucherService $vouchers,
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $members = collect();
        if ($q !== '') {
            $members = Member::query()
                ->with('hotel', 'level')
                ->where(function ($query) use ($q) {
                    $query->where('member_no', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('full_name', 'like', "%{$q}%");
                })
                ->orderBy('full_name')
                ->limit(25)
                ->get();
        }

        return view('staff.index', ['members' => $members, 'q' => $q]);
    }

    public function search(Request $request)
    {
        return redirect()->route('staff.index', ['q' => $request->query('q')]);
    }

    /** QR scan: /staff/scan?t={token} */
    public function scan(Request $request): RedirectResponse
    {
        $token = (string) $request->query('t');

        $card = \App\Models\DigitalCard::where('token', $token)->first();

        if (! $card || ! $card->is_active) {
            return redirect()->route('staff.index')->with('error', 'QR Code tidak valid atau kartu sudah tidak aktif.');
        }

        return redirect()->route('staff.members.show', $card->member_id)->with('success', 'Member ditemukan via QR scan.');
    }

    public function show(Member $member): View
    {
        $member->load(['hotel', 'level', 'activeCard', 'transactions' => fn ($q) => $q->latest('transaction_date')->limit(10)]);

        return view('staff.member', [
            'member' => $member,
            'recentTransactions' => $member->transactions,
            'memberVouchers' => $member->vouchers()->with('type')->orderByDesc('issued_at')->limit(10)->get(),
            'voucherCounts' => $member->vouchers()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    /*
    |------------------------------------------------------------------
    | CPC v2.0 §10 — Penukaran voucer oleh staff (ajukan → approval manajer)
    |------------------------------------------------------------------
    */

    /** Halaman cari voucer by ID Voucer (input manual / scan) atau lihat voucer member. */
    public function vouchers(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $voucher = null;
        $members = collect();

        if ($q !== '') {
            $voucher = $this->vouchers->findByVoucherNo($q);

            if (! $voucher) {
                // Coba cari member — tampilkan seluruh voucer miliknya
                $members = Member::query()
                    ->with(['hotel', 'level', 'vouchers' => fn ($vq) => $vq->where('status', Voucher::STATUS_AVAILABLE)->with('type')])
                    ->where(function ($query) use ($q) {
                        $query->where('member_no', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%")
                            ->orWhere('full_name', 'like', "%{$q}%");
                    })
                    ->orderBy('full_name')
                    ->limit(10)
                    ->get();
            }
        }

        return view('staff.vouchers', [
            'q' => $q,
            'voucher' => $voucher,
            'members' => $members,
            'hotels' => \App\Models\Hotel::where('is_active', true)->get(),
        ]);
    }

    /** Staff mengajukan penukaran voucer — status menjadi PENDING_APPROVAL (menunggu manajer). */
    public function requestVoucherRedemption(Request $request, Voucher $voucher): RedirectResponse
    {
        $validated = $request->validate([
            'hotel_id' => ['required', 'exists:hotels,id'],
            'outlet' => ['required', 'string', 'max:100'],
            'request_note' => ['nullable', 'string', 'max:300'],
        ], [
            'outlet.required' => 'Outlet / lokasi penukaran wajib diisi.',
        ]);

        try {
            $this->vouchers->requestRedemption(
                $voucher,
                $request->user(),
                (int) $validated['hotel_id'],
                $validated['outlet'],
                $validated['request_note'] ?? null,
            );
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('staff.vouchers', ['q' => $voucher->voucher_no])
            ->with('success', "Permintaan penukaran {$voucher->voucher_no} diajukan — menunggu persetujuan manajer.");
    }

    public function redeem(Request $request, Member $member): RedirectResponse
    {
        $validated = $request->validate([
            'visit_date' => ['required', 'date', 'before_or_equal:today'],
            'hotel_id' => ['required', 'exists:hotels,id'],
            'type' => ['required', 'in:' . implode(',', array_keys(Transaction::TYPES))],
            'transaction_no' => ['nullable', 'string', 'max:40'], // referensi manual jika ada
            'outlet' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'counts_as_visit' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'visit_date.required' => 'Tanggal kunjungan wajib diisi.',
            'visit_date.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
            'amount.required' => 'Amount wajib diisi.',
        ]);

        if (! $member->isActive()) {
            return back()->with('error', 'Member tidak aktif — tidak dapat melakukan redeem.');
        }

        $result = $this->membership->redeemVisit($member, $validated, $request->user());

        $message = "Transaksi {$result['transaction']->transaction_no} tersimpan. "
            . 'Total: ' . $member->refresh()->total_visits . ' visit / Rp' . number_format($member->total_spending, 0, ',', '.');

        // Voucer diskon otomatis dari benefit level (mis. F&B 5% utk Classic)
        if ($voucher = $result['discount_voucher'] ?? null) {
            $message .= ' 🎟 Voucer diskon ' . $voucher->voucher_no . ' ('
                . rtrim(rtrim(number_format((float) $voucher->discount_percent, 2, ',', ''), '0'), ',')
                . '% = Rp' . number_format((float) $voucher->discount_amount, 0, ',', '.')
                . ') digenerate — menunggu approval manajer.';
        }

        if ($result['upgraded_to']) {
            $message .= ' 🎉 Level naik ke ' . strtoupper($result['upgraded_to']->name) . '!';
        }

        return redirect()->route('staff.members.show', $member->id)->with('success', $message);
    }
}
