<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToSchool;

class StudentStatusHistory extends Model
{
    use BelongsToSchool;

    protected $table = 'student_status_history';

    protected $fillable = [
        'school_id',
        'student_id', 'previous_status', 'new_status', 'reason', 'effective_date', 'changed_by'
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
