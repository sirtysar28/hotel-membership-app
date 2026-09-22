<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'member_no', 'hotel_id', 'membership_type', 'level_id',
        'full_name', 'email', 'phone', 'phone_country_code', 'dob', 'gender', 'address',
        'id_number', 'id_type', 'company', 'occupation', 'proof_reference',
        'status', 'total_visits', 'total_spending', 'joined_at', 'valid_until',
        'last_activity_date', 'tier_downgraded_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'joined_at' => 'date',
            'valid_until' => 'date',
            'last_activity_date' => 'date',
            'tier_downgraded_at' => 'datetime',
            'total_visits' => 'integer',
            'total_spending' => 'decimal:2',
        ];
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function level()
    {
        return $this->belongsTo(MembershipLevel::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function cards()
    {
        return $this->hasMany(DigitalCard::class);
    }

    public function activeCard()
    {
        return $this->hasOne(DigitalCard::class)->where('is_active', true)->latestOfMany();
    }

    public function benefits()
    {
        return $this->hasMany(MemberBenefit::class);
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function periods()
    {
        return $this->hasMany(MembershipPeriod::class)->orderByDesc('period_no');
    }

    public function activePeriod()
    {
        return $this->hasOne(MembershipPeriod::class)->where('status', 'active')->latestOfMany('period_no');
    }

    public function portalUser()
    {
        return $this->hasOne(User::class, 'member_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isPaid(): bool
    {
        return $this->membership_type === 'paid';
    }

    public function isDiamond(): bool
    {
        return $this->level?->code === 'diamond';
    }

    /** Nomor HP lengkap dengan kode negara, mis. "+62 8123..." */
    public function fullPhone(): string
    {
        return trim(($this->phone_country_code ?: '+62') . ' ' . $this->phone);
    }

    /** CPC §8 — tanggal acuan ketidakaktifan (aktivitas terakhir / gabung). */
    public function inactivityReferenceDate(): \Carbon\CarbonInterface
    {
        return ($this->last_activity_date ?? $this->joined_at) ?? now();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
