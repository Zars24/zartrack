@extends('layouts.admin')
@section('title', $invoice->exists ? 'Edit Invoice' : 'Invoice Baru')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <a href="{{ $invoice->exists ? route('admin.invoices.show', $invoice) : route('admin.invoices.index') }}" class="back-link"><x-icon name="arrow" size="13" />Invoice</a>
        <h1>{{ $invoice->exists ? 'Edit '.$invoice->invoice_number : 'Invoice Baru' }}</h1>
        <p>Pilih order, atur tanggal, pajak, dan total tagihan.</p>
    </div>
</div>
<form method="POST" action="{{ $invoice->exists ? route('admin.invoices.update', $invoice) : route('admin.invoices.store') }}">
    @csrf @if ($invoice->exists) @method('PUT') @endif
    <div class="card">
        <div class="card__body">
            <div class="form-section">
                <div class="form-section__title">Order</div>
                <label class="form-label" for="order_id">Order *</label>
                <select id="order_id" name="order_id" class="form-select" required>
                    <option value="">Pilih order</option>
                    @foreach ($orders as $o)
                        <option value="{{ $o->id }}" @selected(old('order_id', $invoice->order_id ?? $selectedOrder?->id) == $o->id)>
                            {{ $o->order_number }} — {{ $o->customer->name ?? '' }} (Rp {{ number_format($o->total_price, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-section">
                <div class="form-section__title">Tanggal & Nilai</div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label" for="issue_date">Tanggal terbit *</label><input id="issue_date" type="date" name="issue_date" class="form-control" value="{{ old('issue_date', $invoice->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required></div>
                    <div class="col-md-4"><label class="form-label" for="due_date">Jatuh tempo *</label><input id="due_date" type="date" name="due_date" class="form-control" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d') ?? now()->addDays(14)->format('Y-m-d')) }}" required></div>
                    <div class="col-md-4"><label class="form-label" for="tax">Pajak</label><input id="tax" type="number" step="0.01" name="tax" class="form-control" value="{{ old('tax', $invoice->tax ?? 0) }}"></div>
                    <div class="col-md-4"><label class="form-label" for="inv-total">Total (termasuk pajak) *</label><input id="inv-total" type="number" step="0.01" name="total" class="form-control" value="{{ old('total', $invoice->total ?? $selectedOrder?->total_price) }}" required></div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section__title">Catatan</div>
                <label class="form-label visually-hidden" for="notes">Catatan</label>
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Termin, instruksi pembayaran, dsb.">{{ old('notes', $invoice->notes) }}</textarea>
            </div>
        </div>
        <div class="card__foot d-flex gap-2">
            <button class="btn btn-primary"><x-icon name="check" size="15" />Simpan</button>
            <a href="{{ $invoice->exists ? route('admin.invoices.show', $invoice) : route('admin.invoices.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
@endsection