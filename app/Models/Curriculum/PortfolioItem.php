<?php

namespace App\Models\Curriculum;

use App\Models\Academic\AcademicTerm;
use App\Models\Academic\SchoolSubject;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToSchool;

class PortfolioItem extends Model
{
    use SoftDeletes;
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'portfolio_id',
        'category_id',
        'title',
        'description',
        'item_type',
        'content',
        'file_path',
        'url',
        'thumbnail',
        'item_date',
        'subject_id',
        'academic_term_id',
        'tags',
        'reflection',
        'is_featured',
        'is_approved',
        'approved_by',
        'order_index',
    ];

    protected $casts = [
        'tags' => 'json',
        'is_featured' => 'boolean',
        'is_approved' => 'boolean',
        'item_date' => 'date',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function competencies(): BelongsToMany
    {
        return $this->belongsToMany(Competency::class, 'portfolio_item_competencies');
    }

    public function indicators(): BelongsToMany
    {
        return $this->belongsToMany(CompetencyIndicator::class, 'portfolio_item_indicators', 'portfolio_item_id', 'indicator_id');
    }
}
