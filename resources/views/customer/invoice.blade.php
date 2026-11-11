@extends('layouts.portal')
@section('title', $invoice->invoice_number)
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <a href="{{ route('customer.invoices') }}" class="back-link"><x-icon name="arrow" size="13" />Invoice</a>
        <h1 class="mono">{{ $invoice->invoice_number }}</h1>
        <p>Order {{ $invoice->order->order_number }}</p>
    </div>
    <div class="page-head__actions">
        <span class="badge-status {{ $invoice->status === 'paid' ? 'st-completed' : ($invoice->status === 'partial' ? 'st-pickup' : 'st-cancelled') }}">{{ $invoice->status }}</span>
        <a href="{{ route('customer.invoice.pdf', $invoice) }}" class="btn btn-outline-secondary"><x-icon name="download" size="15" />Unduh PDF</a>
    </div>
</div>
<div class="card" style="max-width:720px">
    <div class="card__body">
        <div class="d-flex justify-content-between gap-3 mb-3">
            <div><div class="stat-label">Terbit</div><strong>{{ $invoice->issue_date->format('d M Y') }}</strong></div>
            <div class="text-end"><div class="stat-label">Jatuh tempo</div><strong>{{ $invoice->due_date->format('d M Y') }}</strong></div>
        </div>
        <div class="divider"></div>
        @foreach (['Subtotal' => $invoice->subtotal, 'Pajak' => $invoice->tax, 'Total' => $invoice->total, 'Dibayar' => $invoice->paid_amount, 'Sisa' => $invoice->balance] as $label => $val)
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom fs-13">
                <span class="{{ $label === 'Total' ? 'fw-600' : 'text-dim' }}">{{ $label }}</span>
                <span class="fw-600 num">Rp {{ number_format($val, 0, ',', '.') }}</span>
            </div>
        @endforeach
        @if ($invoice->payments->isNotEmpty())
            <div class="form-section__title mt-3">Pembayaran</div>
            @foreach ($invoice->payments as $p)
                <div class="d-flex justify-content-between fs-12 py-1"><span class="text-dim">{{ $p->paid_at->format('d M Y') }} · {{ ucfirst($p->method) }}</span><span class="num">Rp {{ number_format($p->amount, 0, ',', '.') }}</span></div>
            @endforeach
        @endif
    </div>
</div>
@endsection