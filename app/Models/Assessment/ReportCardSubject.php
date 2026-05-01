<?php

namespace App\Models\Assessment;

use App\Models\Curriculum\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCardSubject extends Model
{
    protected $fillable = [
        'report_card_id', 'subject_id', 'total_marks', 'marks_obtained',
        'percentage', 'grade', 'subject_rank', 'teacher_remarks'
    ];

    public function reportCard(): BelongsTo
    {
        return $this->belongsTo(ReportCard::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
