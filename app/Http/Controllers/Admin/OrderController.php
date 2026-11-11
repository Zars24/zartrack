<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Container;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('customer')
            ->withCount('containers')
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w->where('order_number', 'like', "%$v%")->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%$v%"))))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function create()
    {
        return view('admin.orders.form', [
            'order' => new Order(),
            'customers' => Customer::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();
        $order = Order::create($data);

        ActivityLog::record('order.create', $order, "Order {$order->order_number} dibuat");

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order dibuat. Tambahkan container.');
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'containers.driver', 'containers.truck', 'containers.latestLocation', 'documents', 'invoices']);
        $drivers = \App\Models\Driver::whereIn('status', ['available', 'on_duty'])->orderBy('name')->get();
        $trucks = \App\Models\Truck::whereIn('status', ['available', 'in_use'])->orderBy('plate_number')->get();

        return view('admin.orders.show', compact('order', 'drivers', 'trucks'));
    }

    public function edit(Order $order)
    {
        return view('admin.orders.form', [
            'order' => $order,
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $order->update($this->validated($request));
        ActivityLog::record('order.update', $order, "Order {$order->order_number} diperbarui");

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order diperbarui.');
    }

    public function destroy(Order $order)
    {
        abort_if($order->invoices()->exists(), 422, 'Order memiliki invoice.');
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Order dihapus.');
    }

    public function storeContainer(Request $request, Order $order)
    {
        $data = $request->validate([
            'container_number' => ['required', 'string', 'max:30'],
            'size' => ['required', 'in:'.implode(',', config('zartrack.container_sizes'))],
            'seal_number' => ['nullable', 'string', 'max:30'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $container = $order->containers()->create($data + ['status' => 'pending']);
        $container->timelines()->create(['status' => 'pending', 'note' => 'Container dibuat', 'user_id' => auth()->id()]);
        ActivityLog::record('container.create', $container, "Container {$container->container_number} ditambahkan");

        return back()->with('success', 'Container ditambahkan.');
    }

    public function assign(Request $request, Container $container)
    {
        $data = $request->validate([
            'driver_id' => ['required', 'exists:drivers,id'],
            'truck_id' => ['required', 'exists:trucks,id'],
        ]);

        $container->update($data);

        if ($container->status === 'pending') {
            $container->transitionTo('confirmed', auth()->user(), 'Order dikonfirmasi');
        }
        if ($container->status === 'confirmed') {
            $container->transitionTo('assigned', auth()->user(), 'Driver & truck ditugaskan');
        }

        $container->driver->update(['status' => 'on_duty']);
        $container->truck->update(['status' => 'in_use']);

        ActivityLog::record('container.assign', $container, "Container {$container->container_number} ditugaskan ke {$container->driver->name}");

        return back()->with('success', 'Penugasan disimpan.');
    }

    public function transition(Request $request, Container $container)
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        abort_unless($request->user()->canManageOperations(), 403);

        try {
            $container->transitionTo($data['status'], $request->user(), $data['note'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => 'Transisi status tidak valid.']);
        }

        ActivityLog::record('container.status', $container, "Status container {$container->container_number} -> {$data['status']}");

        return back()->with('success', 'Status diperbarui.');
    }

    public function destroyContainer(Container $container)
    {
        abort_unless(in_array($container->status, ['pending', 'cancelled']), 422, 'Container aktif tidak dapat dihapus.');
        $container->delete();

        return back()->with('success', 'Container dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'service_type' => ['required', 'in:'.implode(',', array_keys(config('zartrack.service_types')))],
            'origin' => ['required', 'string', 'max:150'],
            'destination' => ['required', 'string', 'max:150'],
            'pickup_date' => ['nullable', 'date'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:pickup_date'],
            'total_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
