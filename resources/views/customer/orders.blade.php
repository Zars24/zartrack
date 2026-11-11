@extends('layouts.portal')
@section('title', 'Order Saya')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Portal Customer</span>
        <h1>Order Saya</h1>
        <p>Riwayat pengiriman perusahaan.</p>
    </div>
</div>
<div class="card">
    <div class="table-wrap">
        @if ($orders->isEmpty())
            <div class="empty-state"><x-icon name="order" /><h3>Belum ada order</h3><p class="mb-0">Hubungi tim operasional untuk membuat order.</p></div>
        @else
        <table class="table">
            <thead><tr><th>Order</th><th>Rute</th><th class="text-end">Container</th><th>Status</th><th class="text-end">Tanggal</th></tr></thead>
            <tbody>
            @foreach ($orders as $o)
                <tr>
                    <td><a href="{{ route('customer.orders.show', $o) }}" class="mono fs-12 fw-600">{{ $o->order_number }}</a></td>
                    <td class="fs-13 text-nowrap">{{ $o->origin }} → {{ $o->destination }}</td>
                    <td class="fs-13 text-end num">{{ $o->containers_count }}</td>
                    <td><x-status :status="$o->status" /></td>
                    <td class="fs-12 text-dim text-end text-nowrap">{{ $o->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @if ($orders->hasPages())
        <div class="card__foot">{{ $orders->links() }}</div>
    @endif
</div>
@endsection