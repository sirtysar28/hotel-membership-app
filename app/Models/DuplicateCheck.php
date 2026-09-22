<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DuplicateCheck extends Model
{
    protected $fillable = [
        'submitted_data', 'matched_member_id', 'matched_fields', 'status',
        'resolution_note', 'resolved_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_data' => 'array',
            'matched_fields' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function matchedMember()
    {
        return $this->belongsTo(Member::class, 'matched_member_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
