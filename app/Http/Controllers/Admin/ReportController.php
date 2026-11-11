<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function finance(Request $request)
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now();

        $invoices = Invoice::with('order.customer')
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->latest('issue_date')
            ->get();

        $summary = [
            'invoiced' => $invoices->sum('total'),
            'paid' => $invoices->sum('paid_amount'),
            'outstanding' => $invoices->sum(fn ($i) => $i->balance),
            'count' => $invoices->count(),
        ];

        $byStatus = $invoices->groupBy('status')->map->count();
        $byCustomer = $invoices->groupBy(fn ($i) => $i->order->customer->name ?? '-')
            ->map(fn ($group) => ['total' => $group->sum('total'), 'paid' => $group->sum('paid_amount')])
            ->sortByDesc('total')->take(10);

        return view('admin.reports.finance', compact('invoices', 'summary', 'byStatus', 'byCustomer', 'from', 'to'));
    }

    public function operations(Request $request)
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now();

        $orders = Order::with('customer')->withCount('containers')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->get();

        $byService = $orders->groupBy('service_type')->map->count();
        $byStatus = $orders->groupBy('status')->map->count();
        $topCustomers = $orders->groupBy(fn ($o) => $o->customer->name ?? '-')->map->count()->sortDesc()->take(10);

        return view('admin.reports.operations', compact('orders', 'byService', 'byStatus', 'topCustomers', 'from', 'to'));
    }
}