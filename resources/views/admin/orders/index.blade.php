@extends('layouts.admin')
@section('title', 'Order')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Operasional</span>
        <h1>Order</h1>
        <p>Order pengiriman dan container aktif.</p>
    </div>
    <div class="page-head__actions">
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary"><x-icon name="plus" size="16" />Order Baru</a>
    </div>
</div>
<div class="card">
    <div class="card__head">
        <form class="toolbar" method="GET">
            <div class="field-search toolbar__grow" style="max-width:280px">
                <x-icon name="search" size="15" />
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari order / customer" aria-label="Cari order">
            </div>
            <select name="status" class="form-select" style="max-width:180px" aria-label="Filter status">
                <option value="">Semua status</option>
                @foreach (config('zartrack.status_labels') as $k => $v)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary"><x-icon name="search" size="15" />Filter</button>
            @if (request('q') || request('status'))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        @if ($orders->isEmpty())
            <div class="empty-state"><x-icon name="order" /><h3>Belum ada order</h3><p class="mb-0">Buat order baru untuk memulai pengiriman.</p></div>
        @else
        <table class="table">
            <thead><tr><th>Order</th><th>Customer</th><th>Rute</th><th>Container</th><th>Status</th><th class="text-end">Total</th></tr></thead>
            <tbody>
            @foreach ($orders as $o)
                <tr>
                    <td>
                        <a href="{{ route('admin.orders.show', $o) }}" class="mono fs-12 fw-600">{{ $o->order_number }}</a>
                        <div class="fs-12 text-dim">{{ $o->created_at->format('d M Y') }}</div>
                    </td>
                    <td class="fs-13 text-truncate" style="max-width:170px">{{ $o->customer->name }}</td>
                    <td class="fs-12 text-muted-2 text-nowrap">{{ $o->origin }} → {{ $o->destination }}</td>
                    <td><span class="chip">{{ $o->containers_count }}</span></td>
                    <td><x-status :status="$o->status" /></td>
                    <td class="fs-13 fw-600 text-end num text-nowrap">Rp {{ number_format($o->total_price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @if ($orders->hasPages())
        <div class="card__foot">{{ $orders->links() }}</div>
    @endif
</div>
@endsection