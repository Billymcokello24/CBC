<?php

namespace App\Models\Library;

use App\Models\School;
use App\Models\Curriculum\Subject;
use App\Models\Academic\GradeLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'book_category_id',
        'title',
        'isbn',
        'author',
        'publisher',
        'publication_year',
        'edition',
        'description',
        'cover_image',
        'language',
        'pages',
        'shelf_location',
        'total_copies',
        'available_copies',
        'price',
        'subject_id',
        'grade_level_id',
        'book_type',
        'is_loanable',
        'loan_duration_days',
        'is_active',
    ];

    protected $casts = [
        'is_loanable' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'total_copies' => 'integer',
        'available_copies' => 'integer',
        'loan_duration_days' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function category()
    {
        return $this->belongsTo(BookCategory::class, 'book_category_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function loans()
    {
        return $this->hasManyThrough(BookLoan::class, BookCopy::class, 'book_id', 'book_copy_id', 'id', 'id');
    }
}
