@extends('layouts.admin')
@section('title', $order->exists ? 'Edit Order' : 'Order Baru')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <a href="{{ $order->exists ? route('admin.orders.show', $order) : route('admin.orders.index') }}" class="back-link"><x-icon name="arrow" size="13" />Order</a>
        <h1>{{ $order->exists ? 'Edit Order '.$order->order_number : 'Order Baru' }}</h1>
        <p>Detail customer, rute, dan harga pengiriman.</p>
    </div>
</div>
<form method="POST" action="{{ $order->exists ? route('admin.orders.update', $order) : route('admin.orders.store') }}">
    @csrf @if ($order->exists) @method('PUT') @endif
    <div class="card">
        <div class="card__body">
            <div class="form-section">
                <div class="form-section__title">Customer & Layanan</div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="customer_id">Customer *</label>
                        <select id="customer_id" name="customer_id" class="form-select" required>
                            <option value="">Pilih customer</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}" @selected(old('customer_id', $order->customer_id)==$c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label" for="service_type">Jenis layanan *</label>
                        <select id="service_type" name="service_type" class="form-select">
                            @foreach (config('zartrack.service_types') as $k => $v)
                                <option value="{{ $k }}" @selected(old('service_type', $order->service_type ?? 'trucking')===$k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section__title">Rute & Jadwal</div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="origin">Origin *</label><input id="origin" name="origin" class="form-control" value="{{ old('origin', $order->origin) }}" required placeholder="Tanjung Priok"></div>
                    <div class="col-md-6"><label class="form-label" for="destination">Destination *</label><input id="destination" name="destination" class="form-control" value="{{ old('destination', $order->destination) }}" required placeholder="Cikarang"></div>
                    <div class="col-md-4"><label class="form-label" for="pickup_date">Tanggal pickup</label><input id="pickup_date" type="date" name="pickup_date" class="form-control" value="{{ old('pickup_date', $order->pickup_date?->format('Y-m-d')) }}"></div>
                    <div class="col-md-4"><label class="form-label" for="delivery_date">Tanggal delivery</label><input id="delivery_date" type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', $order->delivery_date?->format('Y-m-d')) }}"></div>
                    <div class="col-md-4"><label class="form-label" for="total_price">Total harga (Rp)</label><input id="total_price" type="number" step="0.01" name="total_price" class="form-control" value="{{ old('total_price', $order->total_price) }}"></div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section__title">Catatan</div>
                <label class="form-label visually-hidden" for="notes">Catatan</label>
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Instruksi khusus, referensi dokumen, dsb.">{{ old('notes', $order->notes) }}</textarea>
            </div>
        </div>
        <div class="card__foot d-flex gap-2">
            <button class="btn btn-primary"><x-icon name="check" size="15" />Simpan</button>
            <a href="{{ $order->exists ? route('admin.orders.show', $order) : route('admin.orders.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
@endsection