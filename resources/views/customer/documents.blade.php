@extends('layouts.portal')
@section('title', 'Dokumen Saya')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Portal Customer</span>
        <h1>Dokumen</h1>
        <p>Delivery order, surat jalan, dan POD.</p>
    </div>
</div>
<div class="card">
    <div class="table-wrap">
        @if ($documents->isEmpty())
            <div class="empty-state"><x-icon name="doc" /><h3>Belum ada dokumen</h3></div>
        @else
        <table class="table">
            <thead><tr><th>Judul</th><th>Tipe</th><th>Order</th><th>Container</th><th>Tanggal</th><th></th></tr></thead>
            <tbody>
            @foreach ($documents as $d)
                <tr>
                    <td class="fs-13 fw-600">{{ $d->title }}</td>
                    <td><span class="badge-status st-confirmed">{{ $d->type_label }}</span></td>
                    <td class="mono fs-12">{{ $d->order->order_number ?? '-' }}</td>
                    <td class="mono fs-12">{{ $d->container->container_number ?? '-' }}</td>
                    <td class="fs-12 text-dim text-nowrap">{{ $d->created_at->format('d M Y') }}</td>
                    <td class="text-end"><a href="{{ route('customer.documents.download', $d) }}" class="btn-icon" aria-label="Unduh dokumen"><x-icon name="download" /></a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @if ($documents->hasPages())
        <div class="card__foot">{{ $documents->links() }}</div>
    @endif
</div>
@endsection