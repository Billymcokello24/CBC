<?php

namespace App\Models\Curriculum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\BelongsToSchool;

class SchemeEntry extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'scheme_id',
        'week_number',
        'lesson_number',
        'strand_id',
        'sub_strand_id',
        'topic',
        'learning_outcomes',
        'learning_activities',
        'resources',
        'assessment',
        'remarks',
    ];

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(SchemeOfWork::class, 'scheme_id');
    }

    public function strand(): BelongsTo
    {
        return $this->belongsTo(Strand::class);
    }

    public function subStrand(): BelongsTo
    {
        return $this->belongsTo(SubStrand::class);
    }
}
