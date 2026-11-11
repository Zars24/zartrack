@extends('layouts.admin')
@section('title', $customer->name)
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <a href="{{ route('admin.customers.index') }}" class="back-link"><x-icon name="arrow" size="13" />Customer</a>
        <h1>{{ $customer->name }}</h1>
        <p>{{ $customer->contact_person ?? 'Tanpa kontak' }} · {{ $customer->phone ?? '-' }}</p>
    </div>
    <div class="page-head__actions">
        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-outline-secondary"><x-icon name="edit" size="15" />Edit</a>
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary"><x-icon name="plus" size="15" />Order</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card__body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="avatar avatar-lg">{{ strtoupper(substr($customer->name, 0, 2)) }}</span>
                    <div class="min-w-0">
                        <div class="fw-700">{{ $customer->name }}</div>
                        <span class="badge-status {{ $customer->status === 'active' ? 'st-completed' : 'st-cancelled' }}">{{ $customer->status }}</span>
                    </div>
                </div>
                <div class="kv">
                    @foreach (['Email' => $customer->email, 'Telepon' => $customer->phone, 'Alamat' => $customer->address, 'Kota' => $customer->city, 'Provinsi' => $customer->province, 'NPWP' => $customer->npwp] as $label => $value)
                        <div class="kv__row"><span class="kv__key">{{ $label }}</span><span class="kv__val">{{ $value ?: '-' }}</span></div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card__head"><h3>Akun Portal</h3><span class="chip">{{ $customer->users->count() }}</span></div>
            <div class="card__body p-0">
                @forelse ($customer->users as $u)
                    <div class="list-row">
                        <span class="avatar avatar-sm">{{ strtoupper(substr($u->name, 0, 2)) }}</span>
                        <span class="min-w-0 flex-grow-1">
                            <span class="d-block fs-13 fw-600">{{ $u->name }}</span>
                            <span class="d-block fs-12 text-dim text-truncate">{{ $u->email }}</span>
                        </span>
                    </div>
                @empty
                    <p class="text-dim fs-13 p-3 mb-0">Belum ada akun. Buat lewat menu User.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card__head"><h3>Order Terbaru</h3><span class="chip">{{ $customer->orders->count() }}</span></div>
            <div class="table-wrap">
                @if ($customer->orders->isEmpty())
                    <div class="empty-state"><x-icon name="order" /><h3>Belum ada order</h3></div>
                @else
                <table class="table">
                    <thead><tr><th>Order</th><th>Rute</th><th>Status</th><th class="text-end">Tanggal</th></tr></thead>
                    <tbody>
                    @foreach ($customer->orders as $o)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $o) }}" class="mono fs-12">{{ $o->order_number }}</a></td>
                            <td class="fs-13 text-nowrap">{{ $o->origin }} → {{ $o->destination }}</td>
                            <td><x-status :status="$o->status" /></td>
                            <td class="fs-12 text-dim text-end">{{ $o->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection