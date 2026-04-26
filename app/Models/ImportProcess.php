<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportProcess extends Model
{
    protected $fillable = [
        'user_id',
        'school_id',
        'type',
        'status',
        'total_rows',
        'processed_rows',
        'error_message'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }
}
