@extends('layouts.admin')
@section('title', $order->order_number)
@php $canOps = auth()->user()->hasRole('super_admin', 'ops_admin'); @endphp

@section('content')
<div class="page-head">
    <div class="page-head__text">
        <a href="{{ route('admin.orders.index') }}" class="back-link"><x-icon name="arrow" size="13" />Order</a>
        <h1 class="mono">{{ $order->order_number }}</h1>
        <p>{{ $order->customer->name }} · {{ config('zartrack.service_types.'.$order->service_type) }}</p>
    </div>
    <div class="page-head__actions">
        <x-status :status="$order->status" />
        @if ($canOps)
            <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-outline-secondary"><x-icon name="edit" size="15" />Edit</a>
        @endif
    </div>
</div>

<div class="card mb-3">
    <div class="card__body d-flex flex-wrap align-items-center gap-3">
        <div class="flex-grow-1" style="min-width:220px">
            <div class="route">
                <div><span class="route__label">{{ $order->origin }}</span></div>
                <div class="route__line"></div>
                <div class="text-end"><span class="route__label">{{ $order->destination }}</span></div>
            </div>
        </div>
        <div class="d-flex gap-3 flex-wrap fs-12 text-dim">
            <span><x-icon name="clock" size="13" /> Pickup {{ $order->pickup_date?->format('d M Y') ?? '—' }}</span>
            <span>Delivery {{ $order->delivery_date?->format('d M Y') ?? '—' }}</span>
            <span class="chip">{{ $order->containers->count() }} container</span>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card mb-3">
            <div class="card__head">
                <h3>Container</h3>
                <span class="chip">{{ $order->containers->count() }}</span>
            </div>
            <div class="card__body">
                @forelse ($order->containers as $c)
                    <div class="card mb-3" style="background:var(--surface-2);box-shadow:none">
                        <div class="card__body">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <span class="mono fw-600">{{ $c->container_number }}</span>
                                <span class="chip">{{ $c->size }} · {{ number_format($c->weight,0,',','.') }} kg</span>
                                <x-status :status="$c->status" class="ms-auto" />
                            </div>
                            <div class="row g-2 fs-12 text-muted-2">
                                <div class="col-sm-4">Driver: <strong class="text-reset">{{ $c->driver->name ?? '—' }}</strong></div>
                                <div class="col-sm-4">Truck: <strong class="mono text-reset">{{ $c->truck->plate_number ?? '—' }}</strong></div>
                                <div class="col-sm-4">Tracking: <a href="{{ route('admin.tracking.show', $c) }}" class="mono">{{ $c->tracking_ref }}</a></div>
                            </div>
                            @if ($canOps)
                                <div class="divider my-3"></div>
                                <div class="d-flex flex-wrap gap-2 align-items-end">
                                    <form method="POST" action="{{ route('admin.containers.assign', $c) }}" class="d-flex flex-wrap gap-2 align-items-end">
                                        @csrf
                                        <div>
                                            <label class="form-label mb-1 fs-12">Driver</label>
                                            <select name="driver_id" class="form-select form-select-sm" required>
                                                <option value="">—</option>
                                                @foreach ($drivers as $d)
                                                    <option value="{{ $d->id }}" @selected($c->driver_id === $d->id)>{{ $d->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label mb-1 fs-12">Truck</label>
                                            <select name="truck_id" class="form-select form-select-sm" required>
                                                <option value="">—</option>
                                                @foreach ($trucks as $t)
                                                    <option value="{{ $t->id }}" @selected($c->truck_id === $t->id)>{{ $t->plate_number }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary">Assign</button>
                                    </form>

                                    @php $allowed = config('zartrack.transitions.'.$c->status, []); @endphp
                                    @if ($allowed)
                                        <form method="POST" action="{{ route('admin.containers.transition', $c) }}" class="d-flex gap-2 align-items-end ms-auto">
                                            @csrf
                                            <select name="status" class="form-select form-select-sm">
                                                @foreach ($allowed as $s)
                                                    <option value="{{ $s }}">{{ config('zartrack.status_labels.'.$s) }}</option>
                                                @endforeach
                                            </select>
                                            <input name="note" class="form-control form-control-sm" placeholder="Catatan" style="max-width:150px">
                                            <button class="btn btn-sm btn-primary">Update</button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state py-3"><x-icon name="container" /><p class="fs-13 mb-0">Belum ada container.</p></div>
                @endforelse

                @if ($canOps)
                <form method="POST" action="{{ route('admin.orders.containers.store', $order) }}" class="row g-2 align-items-end border-top pt-3 mt-3">
                    @csrf
                    <div class="col-sm-3"><label class="form-label">Nomor container *</label><input name="container_number" class="form-control form-control-sm mono" required placeholder="ABCD1234567"></div>
                    <div class="col-sm-2"><label class="form-label">Size</label>
                        <select name="size" class="form-select form-select-sm">
                            @foreach (config('zartrack.container_sizes') as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-sm-2"><label class="form-label">Seal</label><input name="seal_number" class="form-control form-control-sm"></div>
                    <div class="col-sm-2"><label class="form-label">Berat (kg)</label><input type="number" name="weight" class="form-control form-control-sm" min="0"></div>
                    <div class="col-sm-3"><button class="btn btn-sm btn-primary w-100"><x-icon name="plus" size="14" />Tambah Container</button></div>
                </form>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card__head"><h3>Dokumen</h3><span class="chip">{{ $order->documents->count() }}</span></div>
            <div class="card__body p-0">
                @forelse ($order->documents as $doc)
                    <div class="list-row fs-13">
                        <span class="stat-icon" style="width:30px;height:30px;border-radius:8px"><x-icon name="doc" size="15" /></span>
                        <span class="flex-grow-1 min-w-0 text-truncate">{{ $doc->title }}</span>
                        <span class="badge-status st-pending">{{ $doc->type_label }}</span>
                        <a href="{{ route('admin.documents.download', $doc) }}" class="btn-icon" aria-label="Unduh {{ $doc->title }}"><x-icon name="download" /></a>
                    </div>
                @empty
                    <div class="empty-state py-4"><x-icon name="doc" /><p class="fs-13 mb-0">Belum ada dokumen.</p></div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-3">
            <div class="card__head"><h3>Informasi Order</h3></div>
            <div class="card__body">
                <div class="kv">
                    @foreach ([
                        'Rute' => $order->origin.' → '.$order->destination,
                        'Pickup' => $order->pickup_date?->format('d M Y') ?? '-',
                        'Delivery' => $order->delivery_date?->format('d M Y') ?? '-',
                        'Total' => 'Rp '.number_format($order->total_price, 0, ',', '.'),
                        'Dibuat' => $order->created_at->format('d M Y H:i'),
                    ] as $label => $value)
                        <div class="kv__row"><span class="kv__key">{{ $label }}</span><span class="kv__val">{{ $value }}</span></div>
                    @endforeach
                </div>
                @if ($order->notes)
                    <div class="pt-3"><span class="stat-label">Catatan</span><p class="mb-0 mt-1 fs-13">{{ $order->notes }}</p></div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card__head">
                <h3>Invoice</h3>
                @if (auth()->user()->hasRole('super_admin', 'finance'))
                    <a href="{{ route('admin.invoices.create', ['order_id' => $order->id]) }}" class="ms-auto fs-13">Buat <x-icon name="arrow" size="13" /></a>
                @endif
            </div>
            <div class="card__body p-0">
                @forelse ($order->invoices as $inv)
                    <a href="{{ route('admin.invoices.show', $inv) }}" class="list-row">
                        <span class="mono fs-12 flex-grow-1">{{ $inv->invoice_number }}</span>
                        <span class="badge-status {{ $inv->status === 'paid' ? 'st-completed' : ($inv->status === 'partial' ? 'st-pickup' : 'st-cancelled') }}">{{ $inv->status }}</span>
                    </a>
                @empty
                    <p class="text-dim fs-13 p-3 mb-0">Belum ada invoice.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection