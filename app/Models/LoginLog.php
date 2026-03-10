<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'location',
        'status',
        'failure_reason',
        'logged_in_at',
        'logged_out_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
