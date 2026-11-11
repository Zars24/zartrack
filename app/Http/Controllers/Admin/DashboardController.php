<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quote;
use App\Models\Truck;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'orders_total' => Order::count(),
            'orders_active' => Order::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'containers_active' => Container::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'in_transit' => Container::where('status', 'in_transit')->count(),
            'customers' => Customer::active()->count(),
            'drivers_available' => Driver::where('status', 'available')->count(),
            'trucks_available' => Truck::where('status', 'available')->count(),
            'revenue_month' => Invoice::whereMonth('issue_date', now()->month)->whereYear('issue_date', now()->year)->sum('total'),
            'receivable' => Invoice::whereIn('status', ['unpaid', 'partial'])->sum('total') - Invoice::whereIn('status', ['unpaid', 'partial'])->sum('paid_amount'),
            'quotes_new' => Quote::where('status', 'new')->count(),
        ];

        $recentOrders = Order::with('customer')->latest()->take(6)->get();
        $activeContainers = Container::with(['order', 'driver', 'truck'])->whereIn('status', ['pickup', 'in_transit'])->latest()->take(6)->get();
        $statusBreakdown = Container::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.dashboard', compact('stats', 'recentOrders', 'activeContainers', 'statusBreakdown'));
    }
}
