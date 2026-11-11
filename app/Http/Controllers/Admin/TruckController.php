<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use Illuminate\Http\Request;

class TruckController extends Controller
{
    public function index(Request $request)
    {
        $trucks = Truck::query()
            ->when($request->q, fn ($q, $v) => $q->where('plate_number', 'like', "%$v%")->orWhere('type', 'like', "%$v%"))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.trucks.index', compact('trucks'));
    }

    public function create()
    {
        return view('admin.trucks.form', ['truck' => new Truck()]);
    }

    public function store(Request $request)
    {
        Truck::create($this->validated($request));

        return redirect()->route('admin.trucks.index')->with('success', 'Truck dibuat.');
    }

    public function edit(Truck $truck)
    {
        return view('admin.trucks.form', compact('truck'));
    }

    public function update(Request $request, Truck $truck)
    {
        $truck->update($this->validated($request, $truck));

        return redirect()->route('admin.trucks.index')->with('success', 'Truck diperbarui.');
    }

    public function destroy(Truck $truck)
    {
        abort_if($truck->containers()->whereNotIn('status', ['completed', 'cancelled'])->exists(), 422, 'Truck sedang dipakai.');
        $truck->delete();

        return back()->with('success', 'Truck dihapus.');
    }

    private function validated(Request $request, ?Truck $truck = null): array
    {
        return $request->validate([
            'plate_number' => ['required', 'string', 'max:20', 'unique:trucks,plate_number'.($truck ? ','.$truck->id : '')],
            'type' => ['required', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:60'],
            'model' => ['nullable', 'string', 'max:60'],
            'capacity_kg' => ['required', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', 'in:available,in_use,maintenance'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
