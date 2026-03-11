<?php

namespace App\Models\Communication;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'type',
        'priority',
        'target_audience',
        'target_grades',
        'target_classes',
        'start_date',
        'end_date',
        'is_published',
        'is_pinned',
        'created_by',
        'attachments',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_published' => 'boolean',
        'is_pinned' => 'boolean',
        'target_grades' => 'array',
        'target_classes' => 'array',
        'attachments' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
}
