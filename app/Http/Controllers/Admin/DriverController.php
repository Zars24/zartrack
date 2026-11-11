<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $drivers = Driver::with('user')
            ->when($request->q, fn ($q, $v) => $q->where('name', 'like', "%$v%")->orWhere('phone', 'like', "%$v%"))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.drivers.index', compact('drivers'));
    }

    public function create()
    {
        return view('admin.drivers.form', ['driver' => new Driver()]);
    }

    public function store(Request $request)
    {
        $data = collect($this->validated($request))->except(['email', 'password'])->all();

        $driver = Driver::create($data);
        $this->syncUser($driver, $request);

        return redirect()->route('admin.drivers.index')->with('success', 'Driver dibuat.');
    }

    public function edit(Driver $driver)
    {
        return view('admin.drivers.form', compact('driver'));
    }

    public function update(Request $request, Driver $driver)
    {
        $driver->update(collect($this->validated($request, $driver))->except(['email', 'password'])->all());
        $this->syncUser($driver, $request);

        return redirect()->route('admin.drivers.index')->with('success', 'Driver diperbarui.');
    }

    public function destroy(Driver $driver)
    {
        abort_if($driver->containers()->whereNotIn('status', ['completed', 'cancelled'])->exists(), 422, 'Driver punya tugas aktif.');
        $driver->delete();

        return back()->with('success', 'Driver dihapus.');
    }

    private function validated(Request $request, ?Driver $driver = null): array
    {
        $userId = $driver?->user_id;

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'license_number' => ['nullable', 'string', 'max:40'],
            'license_expiry' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:available,on_duty,inactive'],
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email'.($userId ? ','.$userId : '')],
            'password' => ['nullable', 'string', 'min:8'],
        ]);
    }

    /** One driver may be linked to one login account; created lazily. */
    private function syncUser(Driver $driver, Request $request): void
    {
        if (! $request->filled('email')) {
            return;
        }

        $user = $driver->user ?: User::where('email', $request->email)->where('role', 'driver')->first();

        if ($user) {
            $user->update(['name' => $driver->name, 'email' => $request->email, 'phone' => $driver->phone]);
        } else {
            $user = User::create([
                'name' => $driver->name,
                'email' => $request->email,
                'phone' => $driver->phone,
                'role' => 'driver',
                'is_active' => true,
                'password' => Hash::make($request->password ?: 'password'),
            ]);
        }

        $driver->update(['user_id' => $user->id]);
    }
}
