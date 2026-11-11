@extends('layouts.admin')
@section('title', $invoice->invoice_number)
@php $canFinance = auth()->user()->hasRole('super_admin', 'finance'); @endphp
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <a href="{{ route('admin.invoices.index') }}" class="back-link"><x-icon name="arrow" size="13" />Invoice</a>
        <h1 class="mono">{{ $invoice->invoice_number }}</h1>
        <p>{{ $invoice->order->customer->name ?? '-' }} · {{ $invoice->order->order_number }}</p>
    </div>
    <div class="page-head__actions">
        <span class="badge-status {{ $invoice->status === 'paid' ? 'st-completed' : ($invoice->status === 'partial' ? 'st-pickup' : 'st-cancelled') }}">{{ $invoice->status }}</span>
        <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-outline-secondary"><x-icon name="download" size="15" />PDF</a>
        @if ($canFinance)
            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-outline-secondary"><x-icon name="edit" size="15" />Edit</a>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card__head"><h3>Rincian Tagihan</h3></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Deskripsi</th><th class="text-end">Jumlah</th></tr></thead>
                    <tbody>
                        <tr><td>Layanan {{ config('zartrack.service_types.'.$invoice->order->service_type) }} — {{ $invoice->order->origin }} → {{ $invoice->order->destination }}</td><td class="text-end num">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td></tr>
                        <tr><td>Pajak</td><td class="text-end num">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td></tr>
                        <tr class="fw-600"><td>Total</td><td class="text-end num">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td></tr>
                        <tr><td>Dibayar</td><td class="text-end num text-success">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</td></tr>
                        <tr class="fw-600"><td>Sisa</td><td class="text-end num">Rp {{ number_format($invoice->balance, 0, ',', '.') }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card__head"><h3>Pembayaran</h3><span class="chip">{{ $invoice->payments->count() }}</span></div>
            <div class="table-wrap">
                @if ($invoice->payments->isEmpty())
                    <div class="empty-state py-4"><x-icon name="wallet" /><p class="fs-13 mb-0">Belum ada pembayaran.</p></div>
                @else
                <table class="table">
                    <thead><tr><th>Tanggal</th><th>Metode</th><th>Referensi</th><th class="text-end">Jumlah</th></tr></thead>
                    <tbody>
                    @foreach ($invoice->payments as $p)
                        <tr>
                            <td class="fs-12 mono">{{ $p->paid_at->format('d M Y') }}</td>
                            <td class="fs-13">{{ ucfirst($p->method) }}</td>
                            <td class="fs-12 text-muted-2">{{ $p->reference ?? '-' }}</td>
                            <td class="text-end fs-13 fw-600 num">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card__head"><h3>Info</h3></div>
            <div class="card__body">
                <div class="kv">
                    @foreach (['Terbit' => $invoice->issue_date->format('d M Y'), 'Jatuh tempo' => $invoice->due_date->format('d M Y'), 'Order' => $invoice->order->order_number] as $label => $value)
                        <div class="kv__row"><span class="kv__key">{{ $label }}</span><span class="kv__val mono fs-12">{{ $value }}</span></div>
                    @endforeach
                </div>
                @if ($invoice->notes)<p class="mt-3 mb-0 fs-12 text-muted-2">{{ $invoice->notes }}</p>@endif
            </div>
        </div>

        @if ($canFinance && $invoice->balance > 0)
        <div class="card">
            <div class="card__head"><h3>Catat Pembayaran</h3></div>
            <div class="card__body">
                <form method="POST" action="{{ route('admin.invoices.payments', $invoice) }}">
                    @csrf
                    <div class="mb-2"><label class="form-label" for="amount">Jumlah *</label><input id="amount" type="number" step="0.01" name="amount" class="form-control" max="{{ $invoice->balance }}" value="{{ $invoice->balance }}" required></div>
                    <div class="mb-2"><label class="form-label" for="method">Metode *</label>
                        <select id="method" name="method" class="form-select">@foreach (['transfer','cash','cheque'] as $m)<option value="{{ $m }}">{{ ucfirst($m) }}</option>@endforeach</select>
                    </div>
                    <div class="mb-2"><label class="form-label" for="reference">Referensi</label><input id="reference" name="reference" class="form-control"></div>
                    <div class="mb-3"><label class="form-label" for="paid_at">Tanggal *</label><input id="paid_at" type="date" name="paid_at" class="form-control" value="{{ now()->format('Y-m-d') }}" required></div>
                    <button class="btn btn-primary w-100"><x-icon name="check" size="15" />Simpan Pembayaran</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection