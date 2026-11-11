@extends('layouts.portal')
@section('title', 'Dashboard')
@section('content')
@php $statsList = [['Total Order', $stats['total'], 'order', 'brand'], ['Aktif', $stats['active'], 'truck', 'in_transit'], ['Selesai', $stats['completed'], 'check', 'ok'], ['Container', $stats['containers'], 'container', 'warn']]; @endphp
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Portal Customer</span>
        <h1>Dashboard Pengiriman</h1>
        <p>Ringkasan order dan container perusahaan Anda.</p>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach ($statsList as [$label, $val, $icon, $tone])
        <div class="col-6 col-xl-3">
            <div class="card card-hover stat-card h-100">
                <div class="card__body">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                        <span class="stat-label">{{ $label }}</span>
                        <span class="stat-icon"><x-icon :name="$icon" /></span>
                    </div>
                    <div class="stat-value">{{ $val }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card__head"><h3>Pengiriman Aktif</h3><a href="{{ route('customer.orders') }}" class="ms-auto fs-13">Semua <x-icon name="arrow" size="13" /></a></div>
            <div class="card__body p-0">
                @forelse ($activeShipments as $o)
                    <a href="{{ route('customer.orders.show', $o) }}" class="list-row">
                        <span class="stat-icon"><x-icon name="order" /></span>
                        <span class="min-w-0 flex-grow-1">
                            <span class="d-block mono fs-12 fw-600">{{ $o->order_number }}</span>
                            <span class="d-block fs-12 text-dim text-truncate">{{ $o->origin }} → {{ $o->destination }} · {{ $o->containers->count() }} container</span>
                        </span>
                        <x-status :status="$o->status" />
                    </a>
                @empty
                    <div class="empty-state"><x-icon name="truck" /><h3>Tidak ada pengiriman aktif</h3></div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card__head"><h3>Invoice Terbaru</h3><a href="{{ route('customer.invoices') }}" class="ms-auto fs-13">Semua <x-icon name="arrow" size="13" /></a></div>
            <div class="card__body p-0">
                @forelse ($invoices as $i)
                    <a href="{{ route('customer.invoice.show', $i) }}" class="list-row">
                        <span class="stat-icon" style="width:32px;height:32px;border-radius:9px"><x-icon name="invoice" size="16" /></span>
                        <span class="min-w-0 flex-grow-1">
                            <span class="d-block mono fs-12 fw-600">{{ $i->invoice_number }}</span>
                            <span class="d-block fs-12 text-dim num">Rp {{ number_format($i->total, 0, ',', '.') }}</span>
                        </span>
                        <span class="badge-status {{ $i->status === 'paid' ? 'st-completed' : ($i->status === 'partial' ? 'st-pickup' : 'st-cancelled') }}">{{ $i->status }}</span>
                    </a>
                @empty
                    <div class="empty-state py-4"><x-icon name="invoice" /><p class="fs-13 mb-0">Belum ada invoice.</p></div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection