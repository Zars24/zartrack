@extends('layouts.admin')
@section('title', 'Laporan Operasional')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Dokumen & Keuangan</span>
        <h1>Laporan Operasional</h1>
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
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card__head"><h3>Per Layanan</h3></div>
            <div class="card__body">
                @forelse ($byService as $k => $v)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom fs-13"><span class="text-muted-2">{{ config('zartrack.service_types.'.$k, $k) }}</span><strong class="num">{{ $v }}</strong></div>
                @empty <p class="fs-13 text-dim mb-0">Tidak ada data.</p> @endforelse
            </div>
        </div>
        <div class="card">
            <div class="card__head"><h3>Per Status</h3></div>
            <div class="card__body">
                @forelse ($byStatus as $k => $v)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom fs-13"><x-status :status="$k" /><strong class="num">{{ $v }}</strong></div>
                @empty <p class="fs-13 text-dim mb-0">Tidak ada data.</p> @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card__head"><h3>Order</h3><span class="chip">{{ $orders->count() }}</span></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Order</th><th>Customer</th><th class="text-end">Container</th><th>Status</th><th class="text-end">Tanggal</th></tr></thead>
                    <tbody>
                    @forelse ($orders as $o)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $o) }}" class="mono fs-12">{{ $o->order_number }}</a></td>
                            <td class="fs-13 text-truncate" style="max-width:160px">{{ $o->customer->name ?? '-' }}</td>
                            <td class="fs-13 text-end num">{{ $o->containers_count }}</td>
                            <td><x-status :status="$o->status" /></td>
                            <td class="fs-12 text-dim text-end text-nowrap">{{ $o->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state py-4"><p class="fs-13 mb-0">Tidak ada order.</p></div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection