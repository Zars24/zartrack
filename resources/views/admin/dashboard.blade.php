@extends('layouts.admin')
@section('title', 'Dashboard Operasional')

@section('content')
@php
    $isOps = auth()->user()->hasRole('super_admin', 'ops_admin');
    $canCustomer = auth()->user()->hasRole('super_admin', 'ops_admin');
    $maxStatus = max(1, $statusBreakdown->max());
    $cards = [
        ['Order Aktif', $stats['orders_active'], 'order', route('admin.orders.index'), $stats['orders_total'].' order total', 'brand'],
        ['Container Berjalan', $stats['containers_active'], 'container', route('admin.tracking.index'), $stats['in_transit'].' in transit', 'in_transit'],
        ['Customer Aktif', $stats['customers'], 'users', $canCustomer ? route('admin.customers.index') : null, 'terdaftar di sistem', 'ok'],
        ['Piutang', 'Rp '.number_format($stats['receivable'], 0, ',', '.'), 'wallet', route('admin.invoices.index'), 'belum dibayar', 'warn'],
    ];
@endphp

<section class="ops-hero reveal mb-3">
    <div class="ops-hero__inner d-flex flex-wrap align-items-start gap-3">
        <div class="me-auto">
            <span class="ops-hero__eyebrow"><span class="pulse"></span>Live Operations · {{ now()->translatedFormat('l, d F Y') }}</span>
            <h1>Selamat datang, {{ explode(' ', auth()->user()->name)[0] }}.</h1>
            <p>Ringkasan pengiriman container, armada, dan keuangan hari ini.</p>
        </div>
        @if ($isOps)
            <a href="{{ route('admin.orders.create') }}" class="btn btn-primary"><x-icon name="plus" size="16" />Order Baru</a>
        @endif
    </div>
    <div class="ops-hero__metrics">
        <div><small>Revenue bulan ini</small><strong>Rp {{ number_format($stats['revenue_month'], 0, ',', '.') }}</strong></div>
        <div><small>Driver siap</small><strong>{{ $stats['drivers_available'] }}</strong></div>
        <div><small>Armada siap</small><strong>{{ $stats['trucks_available'] }}</strong></div>
        <div><small>Quote baru</small><strong>{{ $stats['quotes_new'] }}</strong></div>
    </div>
</section>

<div class="row g-3 mb-3">
    @foreach ($cards as [$label, $value, $icon, $link, $sub, $tone])
        <div class="col-6 col-xl-3">
            <div class="card card-hover stat-card h-100">
                <div class="card__body">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                        <span class="stat-label">{{ $label }}</span>
                        <span class="stat-icon"><x-icon :name="$icon" /></span>
                    </div>
                    <div class="stat-value">
                        @if ($link)<a href="{{ $link }}" class="text-reset stretched-link">{{ $value }}</a>@else {{ $value }} @endif
                    </div>
                    <div class="fs-12 text-dim mt-1">{{ $sub }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card__head">
                <h3>Order Terbaru</h3>
                <span class="chip">{{ $recentOrders->count() }}</span>
                <a href="{{ route('admin.orders.index') }}" class="ms-auto fs-13">Semua <x-icon name="arrow" size="13" /></a>
            </div>
            <div class="table-wrap">
                @if ($recentOrders->isEmpty())
                    <div class="empty-state"><x-icon name="order" /><h3>Belum ada order</h3><p class="mb-0">Order baru akan muncul di sini.</p></div>
                @else
                    <table class="table">
                        <thead><tr><th>Order</th><th>Customer</th><th>Rute</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach ($recentOrders as $o)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $o) }}" class="mono fs-12 fw-600">{{ $o->order_number }}</a>
                                    <div class="fs-12 text-dim">{{ $o->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="fs-13 text-truncate" style="max-width:150px">{{ $o->customer->name }}</td>
                                <td class="fs-12 text-muted-2 text-nowrap">{{ $o->origin }} → {{ $o->destination }}</td>
                                <td><x-status :status="$o->status" /></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card mb-3">
            <div class="card__head">
                <h3>Status Container</h3>
                <span class="chip ms-auto">{{ $statusBreakdown->sum() }}</span>
            </div>
            <div class="card__body">
                @forelse ($statusBreakdown as $status => $total)
                    <div class="bar-list__row mb-2">
                        <div class="bar-list__top">
                            <x-status :status="$status" />
                            <span class="bar-list__val">{{ $total }}</span>
                        </div>
                        <div class="bar-list__track">
                            <div class="bar-list__fill" style="width: {{ max(4, round($total / $maxStatus * 100)) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-dim fs-13 mb-0">Belum ada data container.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card__head">
                <h3>Dalam Perjalanan</h3>
                <a href="{{ route('admin.tracking.index') }}" class="ms-auto fs-13">Peta <x-icon name="arrow" size="13" /></a>
            </div>
            <div class="card__body p-0">
                @forelse ($activeContainers as $c)
                    <a href="{{ route('admin.tracking.show', $c) }}" class="list-row">
                        <span class="stat-icon" style="width:32px;height:32px;border-radius:9px"><x-icon name="container" size="16" /></span>
                        <span class="min-w-0 flex-grow-1">
                            <span class="d-block mono fs-12 fw-600 text-truncate">{{ $c->container_number }}</span>
                            <span class="d-block fs-12 text-dim text-truncate">{{ $c->driver->name ?? 'Belum ada driver' }}</span>
                        </span>
                        <x-status :status="$c->status" />
                    </a>
                @empty
                    <div class="empty-state py-4"><x-icon name="truck" /><p class="fs-13 mb-0">Tidak ada container berjalan.</p></div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection