<?php

namespace App\Models\Transport;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToSchool;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'registration_number',
        'vehicle_type',
        'make',
        'model',
        'year',
        'color',
        'capacity',
        'fuel_type',
        'insurance_expiry',
        'inspection_expiry',
        'insurance_document',
        'gps_tracker_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'insurance_expiry' => 'date',
        'inspection_expiry' => 'date',
        'capacity' => 'integer',
        'year' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function routes()
    {
        return $this->hasMany(TransportRoute::class, 'vehicle_id');
    }
}
