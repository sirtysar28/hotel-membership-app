<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitRedemption extends Model
{
    protected $fillable = [
        'visit_id', 'member_id', 'staff_id', 'data_before', 'data_after', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'data_before' => 'array',
            'data_after' => 'array',
        ];
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
