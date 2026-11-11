<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('customer')
            ->when($request->role, fn ($q, $v) => $q->where('role', $v))
            ->when($request->q, fn ($q, $v) => $q->where('name', 'like', "%$v%")->orWhere('email', 'like', "%$v%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(), 'customers' => Customer::active()->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', 'in:'.implode(',', array_keys(config('zartrack.roles')))],
            'phone' => ['nullable', 'string', 'max:30'],
            'customer_id' => ['nullable', 'required_if:role,customer', 'exists:customers,id'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = true;
        $user = User::create($data);
        ActivityLog::record('user.create', $user, "User {$user->email} dibuat");

        return redirect()->route('admin.users.index')->with('success', 'User dibuat.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', ['user' => $user, 'customers' => Customer::active()->get()]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:'.implode(',', array_keys(config('zartrack.roles')))],
            'phone' => ['nullable', 'string', 'max:30'],
            'customer_id' => ['nullable', 'required_if:role,customer', 'exists:customers,id'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');
        abort_if($user->id === auth()->id() && ! $data['is_active'], 422, 'Tidak dapat menonaktifkan diri sendiri.');

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User diperbarui.');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 422, 'Tidak dapat menghapus diri sendiri.');
        $user->delete();

        return back()->with('success', 'User dihapus.');
    }

    public function logs()
    {
        $logs = ActivityLog::with('user')->latest('created_at')->paginate(25);

        return view('admin.logs', compact('logs'));
    }
}