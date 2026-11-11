<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentTimeline extends Model
{
    public $timestamps = false;

    protected $fillable = ['container_id', 'status', 'note', 'user_id', 'lat', 'lng', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->created_at ??= now());
    }

    public function container()
    {
        return $this->belongsTo(Container::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return config('zartrack.status_labels.'.$this->status, ucfirst($this->status));
    }
}
