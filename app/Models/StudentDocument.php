<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class StudentDocument extends Model
{
    use SoftDeletes, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'student_id', 'title', 'document_type', 'file_path', 'file_type',
        'file_size', 'issue_date', 'expiry_date', 'notes', 'is_verified', 'uploaded_by'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
