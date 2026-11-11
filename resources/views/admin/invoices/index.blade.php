@extends('layouts.admin')
@section('title', 'Invoice')
@section('content')
@php $canFinance = auth()->user()->hasRole('super_admin', 'finance'); @endphp
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Dokumen & Keuangan</span>
        <h1>Invoice</h1>
        <p>Tagihan dan status pembayaran.</p>
    </div>
    @if ($canFinance)
        <div class="page-head__actions"><a href="{{ route('admin.invoices.create') }}" class="btn btn-primary"><x-icon name="plus" size="16" />Invoice</a></div>
    @endif
</div>
<div class="card">
    <div class="card__head">
        <form class="toolbar" method="GET">
            <div class="field-search toolbar__grow" style="max-width:260px">
                <x-icon name="search" size="15" />
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari invoice / customer" aria-label="Cari invoice">
            </div>
            <select name="status" class="form-select" style="max-width:170px" aria-label="Filter status">
                <option value="">Semua status</option>
                @foreach (['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid'] as $k => $v)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary">Filter</button>
            @if (request('q') || request('status'))
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        @if ($invoices->isEmpty())
            <div class="empty-state"><x-icon name="invoice" /><h3>Belum ada invoice</h3><p class="mb-0">Invoice dibuat dari order yang sudah berjalan.</p></div>
        @else
        <table class="table">
            <thead><tr><th>Invoice</th><th>Customer</th><th>Terbit</th><th class="text-end">Total</th><th class="text-end">Dibayar</th><th>Status</th></tr></thead>
            <tbody>
            @foreach ($invoices as $i)
                <tr>
                    <td><a href="{{ route('admin.invoices.show', $i) }}" class="mono fs-12 fw-600">{{ $i->invoice_number }}</a></td>
                    <td class="fs-13 text-truncate" style="max-width:180px">{{ $i->order->customer->name ?? '-' }}</td>
                    <td class="fs-12 text-dim text-nowrap">{{ $i->issue_date->format('d M Y') }}</td>
                    <td class="fs-13 fw-600 text-end num text-nowrap">Rp {{ number_format($i->total, 0, ',', '.') }}</td>
                    <td class="fs-13 text-end num text-nowrap text-muted-2">Rp {{ number_format($i->paid_amount, 0, ',', '.') }}</td>
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