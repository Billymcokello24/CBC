<?php

namespace App\Models\Transport;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class TransportRoute extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'morning_departure_time',
        'afternoon_departure_time',
        'distance_km',
        'estimated_duration_minutes',
        'fee_amount',
        'vehicle_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'distance_km' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'estimated_duration_minutes' => 'integer',
        'morning_departure_time' => 'string',
        'afternoon_departure_time' => 'string',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function stops()
    {
        return $this->hasMany(RouteStop::class, 'route_id');
    }

    public function allocations()
    {
        return $this->hasMany(StudentTransport::class, 'route_id');
    }
}
