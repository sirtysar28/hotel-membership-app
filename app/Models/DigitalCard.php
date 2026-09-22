<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalCard extends Model
{
    protected $fillable = [
        'member_id', 'card_no', 'token', 'level_name', 'valid_until', 'is_active', 'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
            'issued_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
