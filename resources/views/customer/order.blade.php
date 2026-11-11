@extends('layouts.portal')
@section('title', $order->order_number)
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <a href="{{ route('customer.orders') }}" class="back-link"><x-icon name="arrow" size="13" />Order Saya</a>
        <h1 class="mono">{{ $order->order_number }}</h1>
        <p>{{ $order->origin }} → {{ $order->destination }}</p>
    </div>
    <div class="page-head__actions"><x-status :status="$order->status" /></div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        @foreach ($order->containers as $c)
            <div class="card mb-3">
                <div class="card__head">
                    <h3 class="mono">{{ $c->container_number }}</h3>
                    <x-status :status="$c->status" class="ms-auto" />
                </div>
                <div class="card__body">
                    <div class="row g-2 fs-12 text-muted-2 mb-3">
                        <div class="col-sm-4">Ukuran: <strong class="text-reset">{{ $c->size }}</strong></div>
                        <div class="col-sm-4">Driver: <strong class="text-reset">{{ $c->driver->name ?? '—' }}</strong></div>
                        <div class="col-sm-4">Tracking: <a href="{{ route('public.track.ref', $c->tracking_ref) }}" class="mono" target="_blank" rel="noopener">{{ $c->tracking_ref }}</a></div>
                    </div>
                    <ul class="timeline">
                        @foreach ($c->timelines as $i => $tl)
                            <li class="{{ $i === 0 ? 'current' : 'done' }}">
                                <div class="tl-time">{{ $tl->created_at->format('d M Y H:i') }}</div>
                                <div class="tl-title">{{ $tl->status_label }}</div>
                                @if ($tl->note)<div class="tl-note">{{ $tl->note }}</div>@endif
                            </li>
                        @endforeach
                    </ul>
                    @if ($c->latestLocation)
                        <div class="map-mini map-frame map-frame--inset mt-3" data-lat="{{ $c->latestLocation->lat }}" data-lng="{{ $c->latestLocation->lng }}" data-label="{{ $c->container_number }}" style="height:220px"></div>
                    @endif
                </div>
            </div>
        @endforeach
        @if ($order->containers->isEmpty())
            <div class="card"><div class="empty-state"><x-icon name="container" /><h3>Belum ada container</h3></div></div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card__head"><h3>Info Order</h3></div>
            <div class="card__body">
                <div class="kv">
                    @foreach (['Layanan' => config('zartrack.service_types.'.$order->service_type), 'Pickup' => $order->pickup_date?->format('d M Y') ?? '-', 'Delivery' => $order->delivery_date?->format('d M Y') ?? '-', 'Total' => 'Rp '.number_format($order->total_price, 0, ',', '.')] as $label => $value)
                        <div class="kv__row"><span class="kv__key">{{ $label }}</span><span class="kv__val">{{ $value }}</span></div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card__head"><h3>Dokumen & Invoice</h3></div>
            <div class="card__body">
                @foreach ($order->documents as $d)
                    <div class="d-flex align-items-center gap-2 py-1 fs-13"><x-icon name="doc" size="14" class="text-dim" /><span class="flex-grow-1 text-truncate">{{ $d->title }}</span></div>
                @endforeach
                @foreach ($order->invoices as $inv)
                    <div class="d-flex justify-content-between align-items-center py-2 border-top">
                        <a href="{{ route('customer.invoice.show', $inv) }}" class="mono fs-12">{{ $inv->invoice_number }}</a>
                        <span class="badge-status {{ $inv->status === 'paid' ? 'st-completed' : 'st-cancelled' }}">{{ $inv->status }}</span>
                    </div>
                @endforeach
                @if ($order->documents->isEmpty() && $order->invoices->isEmpty())
                    <p class="text-dim fs-13 mb-0">Belum ada dokumen.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection