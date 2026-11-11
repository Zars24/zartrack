<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'customer_id', 'service_type', 'origin', 'destination',
        'pickup_date', 'delivery_date', 'status', 'total_price', 'notes', 'created_by',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'delivery_date' => 'date',
        'total_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_number ??= 'ORD-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function containers()
    {
        return $this->hasMany(Container::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Order status rolls up from its containers: the least-advanced active one wins. */
    public function syncStatusFromContainers(): void
    {
        $rank = ['pending' => 0, 'confirmed' => 1, 'assigned' => 2, 'pickup' => 3,
            'in_transit' => 4, 'delivered' => 5, 'completed' => 6,
            'on_hold' => 7, 'returning_empty' => 8, 'exception' => 9, 'cancelled' => 10];

        $statuses = $this->containers()->pluck('status')->reject(fn ($s) => $s === 'cancelled');

        if ($statuses->isEmpty()) {
            $next = 'cancelled';
        } else {
            $next = $statuses->sortBy(fn ($s) => $rank[$s] ?? 0)->first();
        }

        if ($next !== $this->status) {
            $this->update(['status' => $next]);
        }
    }

    public function getStatusLabelAttribute(): string
    {
        return config('zartrack.status_labels.'.$this->status, ucfirst($this->status));
    }
}
