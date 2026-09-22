<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Hotel;
use App\Models\RedemptionRequest;
use App\Models\Voucher;
use App\Models\VoucherType;
use App\Services\VoucherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CPC v2.0 §4–§6, §10–§12 — Administrasi voucer:
 * - Daftar voucer (filter status/jenis/hotel, pencarian)
 * - Approval penukaran (manajer menyetujui/menolak + alasan)
 * - Pembatalan voucer
 */
class VoucherController extends Controller
{
    public function __construct(private VoucherService $service) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $status = strtoupper((string) $request->input('status', ''));
        if (! array_key_exists($status, Voucher::STATUSES)) {
            $status = '';
        }
        $typeId = (int) $request->input('type', 0);
        $q = trim((string) $request->input('q', ''));

        $vouchers = Voucher::query()
            ->with(['member.hotel', 'member.level', 'type', 'period', 'requester', 'approver', 'hotel'])
            ->when($status !== '', fn ($vq) => $vq->where('status', $status))
            ->when($typeId > 0, fn ($vq) => $vq->where('voucher_type_id', $typeId))
            ->when($q !== '', function ($vq) use ($q) {
                $vq->where(function ($w) use ($q) {
                    $w->where('voucher_no', 'like', "%{$q}%")
                        ->orWhereHas('member', function ($m) use ($q) {
                            $m->where('member_no', 'like', "%{$q}%")
                                ->orWhere('full_name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%");
                        });
                });
            })
            ->when(! $user->hasGlobalAccess() && $user->hotel_id, function ($vq) use ($user) {
                $vq->whereHas('member', fn ($m) => $m->where('hotel_id', $user->hotel_id));
            })
            ->orderByDesc('issued_at')
            ->orderBy('voucher_no')
            ->paginate(20)
            ->withQueryString();

        $counts = Voucher::query()
            ->when(! $user->hasGlobalAccess() && $user->hotel_id, function ($vq) use ($user) {
                $vq->whereHas('member', fn ($m) => $m->where('hotel_id', $user->hotel_id));
            })
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.vouchers.index', [
            'vouchers' => $vouchers,
            'counts' => $counts,
            'types' => VoucherType::orderBy('sort_order')->get(),
            'status' => $status,
            'typeId' => $typeId,
            'q' => $q,
        ]);
    }

    /** Antrian persetujuan penukaran + riwayat keputusan. */
    public function redemptions(Request $request): View
    {
        $user = $request->user();
        $scopeMember = fn ($mq) => $mq->when(! $user->hasGlobalAccess() && $user->hotel_id, fn ($m) => $m->where('hotel_id', $user->hotel_id));

        $pending = RedemptionRequest::query()
            ->where('status', RedemptionRequest::PENDING)
            ->with(['voucher.type', 'member.level', 'member.hotel', 'requester', 'hotel'])
            ->whereHas('member', $scopeMember)
            ->latest()
            ->paginate(15, ['*'], 'pending_page')
            ->withQueryString();

        $history = RedemptionRequest::query()
            ->whereIn('status', [RedemptionRequest::APPROVED, RedemptionRequest::REJECTED])
            ->with(['voucher.type', 'member', 'requester', 'decider', 'hotel'])
            ->whereHas('member', $scopeMember)
            ->latest('decided_at')
            ->paginate(15, ['*'], 'history_page')
            ->withQueryString();

        return view('admin.vouchers.redemptions', [
            'pending' => $pending,
            'history' => $history,
            'canDecide' => $user->isManagerLike(),
        ]);
    }

    /** Manajer MENYETUJUI penukaran (§10, §12) — validasi di VoucherService. */
    public function approve(Request $request, RedemptionRequest $redemption): RedirectResponse
    {
        try {
            $voucher = $this->service->approveRedemption($redemption, $request->user());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Penukaran {$voucher->voucher_no} DISETUJUI — status REDEEMED & email notifikasi terkirim.");
    }

    /** Manajer MENOLAK penukaran — alasan wajib, voucer kembali AVAILABLE. */
    public function reject(Request $request, RedemptionRequest $redemption): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:300'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi (riwayat penolakan wajib terjaga).',
        ]);

        try {
            $voucher = $this->service->rejectRedemption($redemption, $request->user(), $validated['rejection_reason']);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Permintaan {$voucher->voucher_no} DITOLAK — voucer kembali AVAILABLE. Alasan tercatat.");
    }

    /** Pembatalan voucer (belum pernah ditukarkan). */
    public function cancel(Request $request, Voucher $voucher): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:300'],
        ]);

        try {
            $this->service->cancelVoucher($voucher, $request->user(), $validated['reason'] ?? null);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Voucer {$voucher->voucher_no} dibatalkan.");
    }

    /** AJAX lookup voucer by ID Voucer (dipakai approval scan). */
    public function lookup(string $voucherNo)
    {
        $voucher = $this->service->findByVoucherNo($voucherNo);

        if (! $voucher) {
            return response()->json(['status' => 'not_found']);
        }

        return response()->json([
            'status' => 'found',
            'voucher' => [
                'voucher_no' => $voucher->voucher_no,
                'type' => $voucher->type->name,
                'status' => $voucher->status,
                'member' => $voucher->member->full_name,
                'member_no' => $voucher->member->member_no,
                'expires_at' => $voucher->expires_at?->format('d M Y'),
            ],
        ]);
    }

    /**
     * CPC v2.0 §13 — Slip cetak/keluaran voucer setelah persetujuan manajer.
     * Memuat ID Anggota, ID Voucer, Jenis Voucer, dan info persetujuan/penukaran.
     * View standalone (siap cetak / print-to-PDF via browser).
     */
    public function slip(RedemptionRequest $redemption): View
    {
        abort_unless($redemption->status === RedemptionRequest::APPROVED, 404, 'Slip hanya tersedia untuk penukaran yang disetujui.');

        $redemption->load(['voucher.type', 'member.level', 'member.hotel', 'requester', 'decider', 'hotel']);

        AuditLog::record('voucher_slip_printed', 'Voucher', $redemption->voucher_id,
            "Slip cetak voucer {$redemption->voucher_no} diakses oleh " . auth()->user()->name);

        return view('admin.vouchers.slip', ['redemption' => $redemption]);
    }
}
