@extends('layouts.portal')
@section('title', 'Invoice Saya')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Portal Customer</span>
        <h1>Invoice</h1>
        <p>Tagihan pengiriman perusahaan Anda.</p>
    </div>
</div>
<div class="card">
    <div class="table-wrap">
        @if ($invoices->isEmpty())
            <div class="empty-state"><x-icon name="invoice" /><h3>Belum ada invoice</h3></div>
        @else
        <table class="table">
            <thead><tr><th>Invoice</th><th>Order</th><th>Terbit</th><th class="text-end">Total</th><th>Status</th></tr></thead>
            <tbody>
            @foreach ($invoices as $i)
                <tr>
                    <td><a href="{{ route('customer.invoice.show', $i) }}" class="mono fs-12 fw-600">{{ $i->invoice_number }}</a></td>
                    <td class="mono fs-12">{{ $i->order->order_number }}</td>
                    <td class="fs-12 text-dim text-nowrap">{{ $i->issue_date->format('d M Y') }}</td>
                    <td class="fs-13 text-end num text-nowrap">Rp {{ number_format($i->total, 0, ',', '.') }}</td>
                    <td><span class="badge-status {{ $i->status === 'paid' ? 'st-completed' : ($i->status === 'partial' ? 'st-pickup' : 'st-cancelled') }}">{{ $i->status }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @if ($invoices->hasPages())
        <div class="card__foot">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection