<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipLevel extends Model
{
    protected $fillable = [
        'code', 'name', 'sort_order', 'card_color', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function rules()
    {
        return $this->hasMany(MembershipRule::class, 'level_id');
    }

    public function activeRule()
    {
        return $this->hasOne(MembershipRule::class, 'level_id')->where('is_active', true);
    }

    public function benefits()
    {
        return $this->hasMany(MembershipBenefit::class, 'level_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
