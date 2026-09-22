<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CPC v2.0 §5 — Instans voucer individu dengan ID Voucer unik.
 * Jenis Voucer (voucher_type) = kategori; ID Voucer (voucher_no) = instans spesifik.
 */
class Voucher extends Model
{
    public const STATUS_AVAILABLE = 'AVAILABLE';
    public const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REDEEMED = 'REDEEMED';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_CANCELLED = 'CANCELLED';

    /** CPC v2.0 §11 — daftar lengkap status voucer. APPROVED/REJECTED tercatat
     *  pada keputusan manajer (RedemptionRequest); voucer final REDEEMED / kembali AVAILABLE. */
    public const STATUSES = [
        self::STATUS_AVAILABLE => 'Available',
        self::STATUS_PENDING_APPROVAL => 'Pending Approval',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REDEEMED => 'Redeemed',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_EXPIRED => 'Expired',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    protected $fillable = [
        'voucher_no', 'member_id', 'voucher_type_id', 'voucher_package_id',
        'membership_period_id', 'source', 'status', 'issued_at', 'expires_at',
        'redeemed_by', 'requested_by', 'hotel_id', 'outlet',
        'requested_at', 'redeemed_at', 'expired_marked_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expires_at' => 'date',
            'requested_at' => 'datetime',
            'redeemed_at' => 'datetime',
            'expired_marked_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function type()
    {
        return $this->belongsTo(VoucherType::class, 'voucher_type_id');
    }

    public function package()
    {
        return $this->belongsTo(VoucherPackage::class, 'voucher_package_id');
    }

    public function period()
    {
        return $this->belongsTo(MembershipPeriod::class, 'membership_period_id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }

    public function requests()
    {
        return $this->hasMany(RedemptionRequest::class)->latest();
    }

    public function latestRequest()
    {
        return $this->hasOne(RedemptionRequest::class)->latestOfMany();
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE
            && ($this->expires_at === null || $this->expires_at->endOfDay()->isFuture());
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_AVAILABLE => 'bg-emerald-100 text-emerald-700',
            self::STATUS_PENDING_APPROVAL => 'bg-amber-100 text-amber-700',
            self::STATUS_APPROVED => 'bg-teal-100 text-teal-700',
            self::STATUS_REDEEMED => 'bg-blue-100 text-blue-700',
            self::STATUS_REJECTED => 'bg-rose-100 text-rose-700',
            self::STATUS_EXPIRED => 'bg-gray-200 text-gray-600',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }
}
