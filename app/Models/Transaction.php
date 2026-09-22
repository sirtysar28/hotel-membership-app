<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_no', 'member_id', 'hotel_id', 'type', 'outlet', 'amount',
        'transaction_date', 'counts_as_visit', 'attachment_path', 'notes', 'staff_id',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'counts_as_visit' => 'boolean',
        ];
    }

    public const TYPES = [
        'hotel_stay' => 'Hotel Stay',
        'restaurant' => 'Restaurant',
        'bar' => 'Bar',
        'banquet' => 'Banquet',
        'other_fnb' => 'Other F&B',
        'other' => 'Other',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function visit()
    {
        return $this->hasOne(Visit::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
