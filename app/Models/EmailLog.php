<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'member_id', 'to_email', 'type', 'subject', 'body', 'status', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
