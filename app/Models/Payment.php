<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id', 'member_id', 'amount', 'status', 'method', 'reference_no',
        'proof_path', 'verified_by', 'verified_at', 'paid_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'verified_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public const STATUSES = [
        'pending' => 'Pending',
        'payment_submitted' => 'Payment Submitted',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'expired' => 'Expired',
        'refunded' => 'Refunded',
        'cancelled' => 'Cancelled',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
