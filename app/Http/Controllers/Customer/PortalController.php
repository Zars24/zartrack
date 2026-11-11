<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;

class PortalController extends Controller
{
    public function dashboard()
    {
        $customerId = $this->customerId();

        $orders = Order::with('containers')->where('customer_id', $customerId)->latest()->get();

        $stats = [
            'total' => $orders->count(),
            'active' => $orders->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'completed' => $orders->where('status', 'completed')->count(),
            'containers' => $orders->flatMap->containers->count(),
        ];

        $activeShipments = $orders->whereNotIn('status', ['completed', 'cancelled'])->take(5);
        $invoices = \App\Models\Invoice::whereHas('order', fn ($q) => $q->where('customer_id', $customerId))
            ->latest()->take(5)->get();

        return view('customer.dashboard', compact('orders', 'stats', 'activeShipments', 'invoices'));
    }

    public function orders()
    {
        $orders = Order::withCount('containers')
            ->where('customer_id', $this->customerId())
            ->with('containers')
            ->latest()
            ->paginate(15);

        return view('customer.orders', compact('orders'));
    }

    public function order(Order $order)
    {
        abort_unless($order->customer_id === $this->customerId(), 403);
        $order->load(['containers.driver', 'containers.latestLocation', 'containers.timelines', 'documents', 'invoices']);

        return view('customer.order', compact('order'));
    }

    public function invoices()
    {
        $invoices = \App\Models\Invoice::with('order')
            ->whereHas('order', fn ($q) => $q->where('customer_id', $this->customerId()))
            ->latest()
            ->paginate(15);

        return view('customer.invoices', compact('invoices'));
    }

    public function invoice(\App\Models\Invoice $invoice)
    {
        abort_unless($invoice->order->customer_id === $this->customerId(), 403);
        $invoice->load(['order.customer', 'order.containers', 'payments']);

        return view('customer.invoice', compact('invoice'));
    }

    public function documents()
    {
        $documents = \App\Models\Document::with(['order', 'container'])
            ->whereHas('order', fn ($q) => $q->where('customer_id', $this->customerId()))
            ->latest()
            ->paginate(15);

        return view('customer.documents', compact('documents'));
    }

    private function customerId(): int
    {
        $id = auth()->user()->customer_id;
        abort_unless($id, 403, 'Akun belum terhubung ke customer.');

        return (int) $id;
    }
}