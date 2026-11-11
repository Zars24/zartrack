@extends('layouts.admin')
@section('title', 'Laporan Keuangan')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Dokumen & Keuangan</span>
        <h1>Laporan Keuangan</h1>
        <p>{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</p>
    </div>
    <div class="page-head__actions">
        <form class="d-flex flex-wrap gap-2 align-items-center" method="GET">
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-control form-control-sm" aria-label="Dari tanggal">
            <span class="text-dim fs-12">s/d</span>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-control form-control-sm" aria-label="Sampai tanggal">
            <button class="btn btn-sm btn-outline-secondary">Terapkan</button>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach ([['Total Tagihan', 'Rp '.number_format($summary['invoiced'],0,',','.'), 'invoice'], ['Terbayar', 'Rp '.number_format($summary['paid'],0,',','.'), 'check'], ['Piutang', 'Rp '.number_format($summary['outstanding'],0,',','.'), 'wallet'], ['Jumlah Invoice', $summary['count'], 'doc']] as [$label, $val, $icon])
        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100"><div class="card__body">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-3"><span class="stat-label">{{ $label }}</span><span class="stat-icon"><x-icon :name="$icon" /></span></div>
                <div class="stat-value" style="font-size:1.35rem">{{ $val }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card__head"><h3>Invoice</h3><span class="chip">{{ $invoices->count() }}</span></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Invoice</th><th>Customer</th><th>Tgl</th><th class="text-end">Total</th><th class="text-end">Sisa</th></tr></thead>
                    <tbody>
                    @forelse ($invoices as $i)
                        <tr>
                            <td><a href="{{ route('admin.invoices.show', $i) }}" class="mono fs-12">{{ $i->invoice_number }}</a></td>
                            <td class="fs-13 text-truncate" style="max-width:160px">{{ $i->order->customer->name ?? '-' }}</td>
                            <td class="fs-12 text-dim text-nowrap">{{ $i->issue_date->format('d M') }}</td>
                            <td class="text-end fs-13 num text-nowrap">Rp {{ number_format($i->total, 0, ',', '.') }}</td>
                            <td class="text-end fs-13 num text-nowrap">Rp {{ number_format($i->balance, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state py-4"><p class="fs-13 mb-0">Tidak ada data.</p></div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card__head"><h3>Per Status</h3></div>
            <div class="card__body">
                @foreach (['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid'] as $k => $label)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom fs-13"><span class="text-muted-2">{{ $label }}</span><strong class="num">{{ $byStatus[$k] ?? 0 }}</strong></div>
                @endforeach
            </div>
        </div>
        <div class="card">
            <div class="card__head"><h3>Top Customer</h3></div>
            <div class="card__body">
                @forelse ($byCustomer as $name => $data)
                    <div class="d-flex justify-content-between gap-2 py-2 border-bottom fs-13">
                        <span class="text-truncate">{{ $name }}</span>
                        <span class="ms-2 text-nowrap num fw-600">Rp {{ number_format($data['total'], 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="fs-13 text-dim mb-0">Tidak ada data.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection