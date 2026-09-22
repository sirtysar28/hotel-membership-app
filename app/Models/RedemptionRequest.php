<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CPC v2.0 §10 — Alur penukaran:
 * Staf mengajukan (PENDING_APPROVAL) → Manajer menyetujui (APPROVED) / menolak (REJECTED).
 * Riwayat penolakan + alasan wajib terjaga.
 */
class RedemptionRequest extends Model
{
    public const PENDING = 'PENDING_APPROVAL';
    public const APPROVED = 'APPROVED';
    public const REJECTED = 'REJECTED';

    protected $fillable = [
        'voucher_id', 'member_id', 'voucher_type_id', 'voucher_no',
        'requested_by', 'hotel_id', 'outlet', 'status',
        'request_note', 'decided_by', 'decided_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
        ];
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function type()
    {
        return $this->belongsTo(VoucherType::class, 'voucher_type_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::PENDING => 'bg-amber-100 text-amber-700',
            self::APPROVED => 'bg-emerald-100 text-emerald-700',
            self::REJECTED => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }
}
