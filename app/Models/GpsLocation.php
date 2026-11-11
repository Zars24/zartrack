<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GpsLocation extends Model
{
    protected $fillable = ['container_id', 'driver_id', 'lat', 'lng', 'speed', 'heading', 'recorded_at'];

    protected $casts = [
        'recorded_at' => 'datetime',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public function container()
    {
        return $this->belongsTo(Container::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
