<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'order_id', 'issue_date', 'due_date', 'subtotal', 'tax',
        'total', 'paid_amount', 'status', 'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function recalculate(): void
    {
        $paid = (float) $this->payments()->sum('amount');
        $status = $paid <= 0 ? 'unpaid' : ($paid >= (float) $this->total ? 'paid' : 'partial');

        $this->update(['paid_amount' => $paid, 'status' => $status]);
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->paid_amount);
    }
}
