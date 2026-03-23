<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class RouteStop extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'route_id',
        'stop_name',
        'stop_order',
        'pickup_time',
        'dropoff_time',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'stop_order' => 'integer',
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }
}
