<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\GpsLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function dashboard()
    {
        $driver = auth()->user()->driver;
        abort_unless($driver, 403, 'Akun driver belum terhubung.');

        $order = collect(['pickup', 'in_transit', 'assigned', 'confirmed', 'pending'])
            ->map(fn ($s, $i) => "WHEN '{$s}' THEN {$i}")
            ->implode(' ');

        $tasks = Container::with(['order.customer', 'truck', 'latestLocation'])
            ->where('driver_id', $driver->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderByRaw("CASE status {$order} ELSE 99 END")
            ->get();

        $done = Container::where('driver_id', $driver->id)->where('status', 'completed')->count();
        $total = Container::where('driver_id', $driver->id)->count();

        return view('driver.dashboard', compact('driver', 'tasks', 'done', 'total'));
    }

    public function show(Container $container)
    {
        $this->authorizeContainer($container);
        $container->load(['order.customer', 'truck', 'timelines.user', 'documents']);

        return view('driver.task', compact('container'));
    }

    public function updateStatus(Request $request, Container $container)
    {
        $this->authorizeContainer($container);

        $data = $request->validate([
            'status' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:500'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        try {
            $container->transitionTo($data['status'], $request->user(), $data['note'] ?? null, $data['lat'] ?? null, $data['lng'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => 'Transisi status tidak valid.']);
        }

        if (($data['lat'] ?? null) !== null) {
            $container->locations()->create([
                'driver_id' => $container->driver_id,
                'lat' => $data['lat'],
                'lng' => $data['lng'] ?? 0,
                'recorded_at' => now(),
            ]);
        }

        \App\Models\ActivityLog::record('driver.status', $container, "Driver update {$container->container_number} -> {$data['status']}");

        return back()->with('success', 'Status perjalanan diperbarui.');
    }

    public function ping(Request $request, Container $container)
    {
        $this->authorizeContainer($container);
        abort_unless(in_array($container->status, ['pickup', 'in_transit'], true), 422, 'GPS hanya aktif selama perjalanan.');

        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'speed' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'heading' => ['nullable', 'numeric', 'min:0', 'max:360'],
        ]);

        $location = $container->locations()->create($data + [
            'driver_id' => $container->driver_id,
            'recorded_at' => now(),
        ]);

        return response()->json(['ok' => true, 'id' => $location->id]);
    }

    public function uploadDocument(Request $request, Container $container)
    {
        $this->authorizeContainer($container);

        $data = $request->validate([
            'type' => ['required', 'in:pod,surat_jalan,other'],
            'title' => ['required', 'string', 'max:150'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $container->documents()->create([
            'order_id' => $container->order_id,
            'type' => $data['type'],
            'title' => $data['title'],
            'file_path' => $request->file('file')->store('documents', 'public'),
            'uploaded_by' => auth()->id(),
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Dokumen diunggah.');
    }

    public function reportIssue(Request $request, Container $container)
    {
        $this->authorizeContainer($container);

        $data = $request->validate(['note' => ['required', 'string', 'max:1000']]);

        $container->transitionTo('exception', $request->user(), 'Kendala: '.$data['note']);

        \App\Models\ActivityLog::record('driver.issue', $container, "Kendala dilaporkan: {$data['note']}");

        return back()->with('success', 'Kendala dilaporkan ke operasional.');
    }

    private function authorizeContainer(Container $container): void
    {
        $driver = auth()->user()->driver;
        abort_unless($driver && $container->driver_id === $driver->id, 403);
    }
}