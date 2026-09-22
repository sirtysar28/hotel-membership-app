<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Member;
use App\Models\MembershipPeriod;
use App\Models\RedemptionRequest;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherPackage;
use App\Models\VoucherType;
use Illuminate\Support\Facades\DB;

/**
 * CPC v2.0 §4–§6, §10–§13 — VoucherService
 * ----------------------------------------
 * - Penerbitan paket voucer dari komposisi configurable (default 12, tanpa hard-code).
 * - ID Voucer unik per instans: VCH-YYPPPP-NNNN (YY=2 digit tahun, PPPP=urutan periode penerbitan, NNNN=urutan voucer).
 * - Voucer mengikuti masa berlaku periode keanggotaan; tidak bisa diakumulasikan ke periode berikutnya.
 * - Alur penukaran: staff AJUKAN (PENDING_APPROVAL) -> manajer SETUJUI (REDEEMED) / TOLAK (kembali AVAILABLE).
 * - Staff tidak bisa menyetujui permintaan sendiri; approval wajib akun manajer.
 */
class VoucherService
{
    public function __construct(private EmailService $email) {}

    /*
    |----------------------------------------------------------------------
    | Penerbitan paket voucer (§4, §15)
    |----------------------------------------------------------------------
    */

    /**
     * Terbitkan paket voucer untuk member sesuai komposisi paket default
     * (paid: paket Diamond; free: paket registrasi gratis — update #17).
     */
    public function issuePackage(Member $member, ?MembershipPeriod $period, string $source = 'registration'): array
    {
        $membershipType = $member->membership_type; // paid | free
        $package = VoucherPackage::defaultFor($membershipType);

        if (! $package) {
            AuditLog::record('voucher_package_missing', 'Member', $member->id,
                "Tidak ada paket voucer default utk tipe {$membershipType} — voucer tidak diterbitkan");

            return [];
        }

        return $this->issueFromPackage($member, $package, $period, $source);
    }

    /** Terbitkan voucer dari paket tertentu. Return koleksi Voucher terbit. */
    public function issueFromPackage(Member $member, VoucherPackage $package, ?MembershipPeriod $period, string $source = 'registration'): array
    {
        return DB::transaction(function () use ($member, $package, $period, $source) {
            $expiresAt = $period?->end_date; // §6 — ikut masa berlaku periode keanggotaan
            $sequence = $this->nextIssueSequence();
            $prefix = 'VCH-' . now()->format('y') . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

            $issued = [];
            $counter = 0;

            foreach ($package->itemsWithType()->get() as $item) {
                if ($item->qty <= 0 || ! $item->type?->is_active) {
                    continue;
                }

                for ($i = 0; $i < $item->qty; $i++) {
                    $counter++;
                    $issued[] = Voucher::create([
                        'voucher_no' => $prefix . '-' . str_pad((string) $counter, 4, '0', STR_PAD_LEFT),
                        'member_id' => $member->id,
                        'voucher_type_id' => $item->voucher_type_id,
                        'voucher_package_id' => $package->id,
                        'membership_period_id' => $period?->id,
                        'source' => $source,
                        'status' => Voucher::STATUS_AVAILABLE,
                        'issued_at' => now(),
                        'expires_at' => $expiresAt,
                    ]);
                }
            }

            AuditLog::record('vouchers_issued', 'Member', $member->id,
                "Terbitkan {$counter} voucer ({$package->name}) utk {$member->member_no}" .
                ($period ? " — periode {$period->label()}" : ''), [
                    'package' => $package->name,
                    'count' => $counter,
                    'expires_at' => $expiresAt?->toDateString(),
                ]);

            return $issued;
        });
    }

    /** Urutan penerbitan global (VCH-YYPPPP): max sequence yang pernah dipakai + 1. */
    private function nextIssueSequence(): int
    {
        // Format: VCH-YY PPPP-NNNN → urutan penerbitan ada di posisi 7–10 (setelah "VCH-" + 2 digit tahun)
        $max = Voucher::where('voucher_no', 'like', 'VCH-' . now()->format('y') . '%')
            ->selectRaw("MAX(CAST(SUBSTRING(voucher_no, 7, 4) AS UNSIGNED)) as max_seq")
            ->value('max_seq') ?? 0;

        return $max + 1;
    }

    /*
    |----------------------------------------------------------------------
    | Alur penukaran (§10)
    |----------------------------------------------------------------------
    */

    /**
     * Staff mengajukan penukaran: validasi member, status, kedaluwarsa,
     * riwayat penukaran -> simpan RedemptionRequest -> voucer PENDING_APPROVAL.
     */
    public function requestRedemption(Voucher $voucher, User $staff, ?int $hotelId = null, ?string $outlet = null, ?string $note = null): RedemptionRequest
    {
        return DB::transaction(function () use ($voucher, $staff, $hotelId, $outlet, $note) {
            $voucher = Voucher::lockForUpdate()->findOrFail($voucher->id);

            // Validasi (§10): member aktif, voucer AVAILABLE, belum kedaluwarsa, belum pernah ditukarkan
            if (! $voucher->isAvailable()) {
                throw new \DomainException("Voucer {$voucher->voucher_no} tidak tersedia (status: {$voucher->status}).");
            }

            if ($voucher->member->status !== 'active') {
                throw new \DomainException('Status member tidak aktif — penukaran tidak dapat diajukan.');
            }

            $request = RedemptionRequest::create([
                'voucher_id' => $voucher->id,
                'member_id' => $voucher->member_id,
                'voucher_type_id' => $voucher->voucher_type_id,
                'voucher_no' => $voucher->voucher_no,
                'requested_by' => $staff->id,
                'hotel_id' => $hotelId ?? $staff->hotel_id,
                'outlet' => $outlet,
                'status' => RedemptionRequest::PENDING,
                'request_note' => $note,
            ]);

            $voucher->update([
                'status' => Voucher::STATUS_PENDING_APPROVAL,
                'requested_by' => $staff->id,
                'requested_at' => now(),
                'hotel_id' => $hotelId ?? $staff->hotel_id,
                'outlet' => $outlet,
            ]);

            AuditLog::record('redemption_requested', 'Voucher', $voucher->id,
                "Permintaan penukaran {$voucher->voucher_no} ({$voucher->type->name}) oleh staff {$staff->name} — MENUNGGU PERSETUJUAN", [
                    'member_no' => $voucher->member->member_no,
                    'hotel' => $voucher->hotel?->name,
                    'outlet' => $outlet,
                ]);

            return $request;
        });
    }

    /**
     * Manajer MENYETUJUI (§10, §12): voucer -> REDEEMED, slip cetak siap,
     * email notifikasi ke tamu/anggota.
     */
    public function approveRedemption(RedemptionRequest $request, User $manager): Voucher
    {
        return DB::transaction(function () use ($request, $manager) {
            $request = RedemptionRequest::lockForUpdate()->findOrFail($request->id);

            if ($request->status !== RedemptionRequest::PENDING) {
                throw new \DomainException('Permintaan sudah diputuskan (status: ' . $request->status . ').');
            }

            // §12 — persetujuan wajib akun manajer, bukan staff pengaju
            if (! $manager->isManagerLike()) {
                throw new \DomainException('Hanya manajer yang berwenang menyetujui penukaran voucer.');
            }

            if ($manager->id === $request->requested_by) {
                throw new \DomainException('Staf tidak dapat menyetujui permintaan penukaran sendiri.');
            }

            $voucher = $request->voucher->refresh();

            if (! in_array($voucher->status, [Voucher::STATUS_PENDING_APPROVAL])) {
                throw new \DomainException("Voucer {$voucher->voucher_no} tidak dalam status MENUNGGU_PERSETUJUAN.");
            }

            $request->update([
                'status' => RedemptionRequest::APPROVED,
                'decided_by' => $manager->id,
                'decided_at' => now(),
            ]);

            $voucher->update([
                'status' => Voucher::STATUS_REDEEMED,
                'redeemed_by' => $manager->id,
                'redeemed_at' => now(),
            ]);

            AuditLog::record('redemption_approved', 'Voucher', $voucher->id,
                "Penukaran {$voucher->voucher_no} DISETUJUI manajer {$manager->name} — DITUKARKAN", [
                    'member_no' => $voucher->member->member_no,
                    'approved_by' => $manager->email,
                    'outlet' => $request->outlet,
                ]);

            // §14 — notifikasi email dikirim SETELAH persetujuan berhasil
            $this->email->sendRedemptionApproved($voucher->member, $voucher, $request, $manager);

            return $voucher->refresh();
        });
    }

    /**
     * Manajer MENOLAK (§10): riwayat penolakan + alasan dipertahankan;
     * voucer kembali AVAILABLE (tidak dianggap ditukarkan).
     */
    public function rejectRedemption(RedemptionRequest $request, User $manager, ?string $reason = null): Voucher
    {
        return DB::transaction(function () use ($request, $manager, $reason) {
            $request = RedemptionRequest::lockForUpdate()->findOrFail($request->id);

            if ($request->status !== RedemptionRequest::PENDING) {
                throw new \DomainException('Permintaan sudah diputuskan.');
            }

            if (! $manager->isManagerLike() || $manager->id === $request->requested_by) {
                throw new \DomainException('Hanya manajer (selain pengaju) yang dapat menolak permintaan.');
            }

            $request->update([
                'status' => RedemptionRequest::REJECTED,
                'decided_by' => $manager->id,
                'decided_at' => now(),
                'rejection_reason' => $reason ?: 'Tidak disebutkan',
            ]);

            $voucher = $request->voucher;
            $voucher->update([
                'status' => Voucher::STATUS_AVAILABLE,
                'requested_by' => null,
                'requested_at' => null,
            ]);

            AuditLog::record('redemption_rejected', 'Voucher', $voucher->id,
                "Permintaan penukaran {$voucher->voucher_no} DITOLAK manajer {$manager->name}. Alasan: " . ($reason ?: '-'), [
                    'member_no' => $voucher->member->member_no,
                ]);

            return $voucher->refresh();
        });
    }

    /*
    |----------------------------------------------------------------------
    | Kedaluwarsa & pembatalan (§6, §11)
    |----------------------------------------------------------------------
    */

    /**
     * Tandai voucer kedaluwarsa (dipanggil scheduler harian):
     * semua voucer belum DITUKARKAN whose expires_at < hari ini -> EXPIRED.
     */
    public function expireDueVouchers(): int
    {
        $count = Voucher::whereIn('status', [Voucher::STATUS_AVAILABLE, Voucher::STATUS_PENDING_APPROVAL])
            ->whereDate('expires_at', '<', today())
            ->update([
                'status' => Voucher::STATUS_EXPIRED,
                'expired_marked_at' => now(),
            ]);

        if ($count > 0) {
            AuditLog::record('vouchers_expired', 'Voucher', null,
                "{$count} voucer ditandai EXPIRED otomatis (akhir periode keanggotaan)");
        }

        return $count;
    }

    /** Pembatalan voucer oleh yang berwenang (admin). */
    public function cancelVoucher(Voucher $voucher, User $by, ?string $reason = null): Voucher
    {
        if ($voucher->status === Voucher::STATUS_REDEEMED) {
            throw new \DomainException('Voucer yang sudah ditukarkan tidak dapat dibatalkan.');
        }

        $voucher->update([
            'status' => Voucher::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        AuditLog::record('voucher_cancelled', 'Voucher', $voucher->id,
            "Voucer {$voucher->voucher_no} dibatalkan oleh {$by->name}. Alasan: " . ($reason ?: '-'));

        return $voucher->refresh();
    }

    /** Cari voucer by ID Voucer utk validasi staff (scan/manual input). */
    public function findByVoucherNo(string $voucherNo): ?Voucher
    {
        return Voucher::with(['member.hotel', 'member.level', 'type'])
            ->where('voucher_no', trim(strtoupper($voucherNo)))
            ->first();
    }
}
