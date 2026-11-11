<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Container extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'container_number', 'size', 'seal_number', 'weight', 'status',
        'driver_id', 'truck_id', 'tracking_ref', 'pickup_at', 'delivered_at', 'notes',
    ];

    protected $casts = [
        'pickup_at' => 'datetime',
        'delivered_at' => 'datetime',
        'weight' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Container $container) {
            $container->tracking_ref ??= 'ZT'.strtoupper(Str::random(10));
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }

    public function timelines()
    {
        return $this->hasMany(ShipmentTimeline::class)->latest('created_at');
    }

    public function locations()
    {
        return $this->hasMany(GpsLocation::class)->latest('recorded_at');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function latestLocation()
    {
        return $this->hasOne(GpsLocation::class)->latestOfMany('recorded_at');
    }

    public function getStatusLabelAttribute(): string
    {
        return config('zartrack.status_labels.'.$this->status, ucfirst($this->status));
    }

    /** Validated status change; single write path for timeline + derived fields. */
    public function transitionTo(string $to, ?User $actor = null, ?string $note = null, ?float $lat = null, ?float $lng = null): void
    {
        $allowed = config('zartrack.transitions.'.$this->status, []);

        if (! in_array($to, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid transition {$this->status} -> {$to}");
        }

        $this->update([
            'status' => $to,
            'pickup_at' => $to === 'pickup' ? now() : $this->pickup_at,
            'delivered_at' => $to === 'delivered' ? now() : $this->delivered_at,
        ]);

        $this->timelines()->create([
            'status' => $to,
            'note' => $note,
            'user_id' => $actor?->id,
            'lat' => $lat,
            'lng' => $lng,
        ]);

        $this->order->syncStatusFromContainers();

        // Notify customer portal users of the owning company.
        $this->order->customer?->users()
            ->where('role', 'customer')
            ->where('is_active', true)
            ->get()
            ->each(fn ($u) => $u->notify(new \App\Notifications\ShipmentStatusChanged($this)));

        // Exceptions need ops attention.
        if ($to === 'exception') {
            User::whereIn('role', ['super_admin', 'ops_admin'])
                ->where('is_active', true)
                ->get()
                ->each(fn ($u) => $u->notify(new \App\Notifications\OperationalAlert(
                    'Kendala container '.$this->container_number,
                    $note ?: 'Container dilaporkan bermasalah.',
                    route('admin.tracking.show', $this->id),
                )));
        }

        if ($this->delivered_at) {
            $this->driver?->update(['status' => 'available']);
            $this->truck?->update(['status' => 'available']);
        }
    }
}
