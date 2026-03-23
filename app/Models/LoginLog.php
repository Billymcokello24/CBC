<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class LoginLog extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
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
