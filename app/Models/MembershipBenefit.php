<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipBenefit extends Model
{
    protected $fillable = [
        'level_id', 'hotel_id', 'name', 'category', 'description', 'value', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function level()
    {
        return $this->belongsTo(MembershipLevel::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
