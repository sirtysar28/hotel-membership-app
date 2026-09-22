<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberBenefit extends Model
{
    protected $fillable = [
        'member_id', 'benefit_id', 'status', 'granted_at', 'expires_at', 'redeemed_at', 'redeemed_by',
    ];

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
            'expires_at' => 'datetime',
            'redeemed_at' => 'datetime',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function benefit()
    {
        return $this->belongsTo(MembershipBenefit::class, 'benefit_id');
    }
}
