<?php

namespace App\Models\Hostel;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class Hostel extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'gender',
        'description',
        'address',
        'warden_name',
        'warden_phone',
        'total_capacity',
        'current_occupancy',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_capacity' => 'integer',
        'current_occupancy' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function allocations()
    {
        return $this->hasMany(HostelAllocation::class);
    }
}
