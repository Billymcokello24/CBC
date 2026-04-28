<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportProcess extends Model
{
    protected $fillable = [
        'user_id',
        'school_id',
        'type',
        'status',
        'file_path',
        'file_name',
        'error_message',
        'filters'
    ];

    protected $casts = [
        'filters' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
