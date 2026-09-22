<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipRule extends Model
{
    protected $fillable = [
        'level_id', 'min_visit', 'max_visit', 'min_spending',
        'evaluation_period_months', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_visit' => 'integer',
            'max_visit' => 'integer',
            'min_spending' => 'decimal:2',
            'evaluation_period_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function level()
    {
        return $this->belongsTo(MembershipLevel::class);
    }
}
