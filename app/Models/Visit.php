<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'member_id', 'hotel_id', 'transaction_id', 'visit_date', 'staff_id',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function redemption()
    {
        return $this->hasOne(VisitRedemption::class);
    }
}
