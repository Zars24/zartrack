<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w->where('name', 'like', "%$v%")->orWhere('email', 'like', "%$v%")))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.form', ['customer' => new Customer()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $customer = Customer::create($data);
        \App\Models\ActivityLog::record('customer.create', $customer, "Customer {$customer->name} dibuat");

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer dibuat.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['orders' => fn ($q) => $q->latest()->take(10), 'users']);
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request));
        \App\Models\ActivityLog::record('customer.update', $customer, "Customer {$customer->name} diperbarui");

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        abort_if($customer->orders()->exists(), 422, 'Customer memiliki order aktif.');
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'npwp' => ['nullable', 'string', 'max:40'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
