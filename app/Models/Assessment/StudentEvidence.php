<?php

namespace App\Models\Assessment;

use App\Models\Student;
use App\Models\User;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEvidence extends Model
{
    use BelongsToSchool;

    protected $table = 'student_evidence';

    protected $fillable = [
        'school_id',
        'student_id',
        'assessment_item_id',
        'file_path',
        'mime_type',
        'file_name',
        'description',
        'uploaded_by',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function assessmentItem(): BelongsTo
    {
        return $this->belongsTo(AssessmentItem::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
