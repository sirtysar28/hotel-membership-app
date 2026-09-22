<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * CPC v2.0 §3, §15 — Periode keanggotaan (Diamond berbayar / free).
 * Periode #1 = 22 Sep 2026–21 Sep 2027; Periode #2 = 22 Sep 2027–21 Sep 2028; dst.
 */
class MembershipPeriod extends Model
{
    protected $fillable = [
        'member_id', 'period_no', 'membership_type', 'price',
        'start_date', 'end_date', 'status', 'payment_reference', 'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'expired_at' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function label(): string
    {
        return "#{$this->period_no} · {$this->start_date->format('d M Y')} – {$this->end_date->format('d M Y')}";
    }
}
