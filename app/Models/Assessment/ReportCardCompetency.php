<?php

namespace App\Models\Assessment;

use App\Models\Curriculum\Competency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCardCompetency extends Model
{
    protected $fillable = [
        'report_card_id', 'competency_id', 'rating', 'comments'
    ];

    public function reportCard(): BelongsTo
    {
        return $this->belongsTo(ReportCard::class);
    }

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class);
    }
}
