<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class SchoolDocument extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $fillable = [
        'school_id', 'title', 'document_type', 'file_path', 'file_type', 'file_size',
        'issue_date', 'expiry_date', 'notes', 'is_verified', 'verified_by', 'verified_at'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
