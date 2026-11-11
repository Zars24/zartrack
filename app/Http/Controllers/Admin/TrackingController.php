<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Container;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $containers = Container::with(['order.customer', 'driver', 'truck', 'latestLocation'])
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->q, fn ($q, $v) => $q->where('container_number', 'like', "%$v%")->orWhere('tracking_ref', 'like', "%$v%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $live = Container::with(['order', 'driver', 'latestLocation'])
            ->whereIn('status', ['pickup', 'in_transit'])
            ->whereHas('latestLocation')
            ->get();

        return view('admin.tracking.index', compact('containers', 'live'));
    }

    public function show(Container $container)
    {
        $container->load(['order.customer', 'driver', 'truck', 'timelines.user', 'locations' => fn ($q) => $q->latest('recorded_at')->take(100), 'documents']);

        return view('admin.tracking.show', compact('container'));
    }
}
