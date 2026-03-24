<?php

namespace App\Models\Events;

use App\Models\School;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class Club extends Model
{
    use HasFactory, SoftDeletes, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'category',
        'logo',
        'motto',
        'patron_id',
        'assistant_patron_id',
        'meeting_day',
        'meeting_time',
        'meeting_venue',
        'membership_fee',
        'max_members',
        'requires_approval',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_approval' => 'boolean',
        'membership_fee' => 'decimal:2',
        'max_members' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function patron()
    {
        return $this->belongsTo(Teacher::class, 'patron_id');
    }

    public function assistantPatron()
    {
        return $this->belongsTo(Teacher::class, 'assistant_patron_id');
    }

    public function members()
    {
        return $this->hasMany(ClubMembership::class);
    }
}
