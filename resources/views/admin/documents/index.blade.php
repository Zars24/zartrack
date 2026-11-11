@extends('layouts.admin')
@section('title', 'Dokumen')
@section('content')
@php $canWrite = auth()->user()->hasRole('super_admin', 'ops_admin', 'doc_admin'); @endphp
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Dokumen & Keuangan</span>
        <h1>Dokumen</h1>
        <p>Delivery order, surat jalan, dan POD.</p>
    </div>
    @if ($canWrite)
        <div class="page-head__actions"><a href="{{ route('admin.documents.create') }}" class="btn btn-primary"><x-icon name="plus" size="16" />Upload</a></div>
    @endif
</div>
<div class="card">
    <div class="card__head">
        <form class="toolbar" method="GET">
            <div class="field-search toolbar__grow" style="max-width:240px">
                <x-icon name="search" size="15" />
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari judul" aria-label="Cari dokumen">
            </div>
            <select name="type" class="form-select" style="max-width:200px" aria-label="Filter tipe">
                <option value="">Semua tipe</option>
                @foreach (config('zartrack.document_types') as $k => $v)
                    <option value="{{ $k }}" @selected(request('type') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary">Filter</button>
            @if (request('q') || request('type'))
                <a href="{{ route('admin.documents.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        @if ($documents->isEmpty())
            <div class="empty-state"><x-icon name="doc" /><h3>Belum ada dokumen</h3><p class="mb-0">Upload DO, surat jalan, atau POD di sini.</p></div>
        @else
        <table class="table">
            <thead><tr><th>Judul</th><th>Tipe</th><th>Order</th><th>Container</th><th>Diunggah</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @foreach ($documents as $d)
                <tr>
                    <td class="fw-600 fs-13">{{ $d->title }}</td>
                    <td><span class="badge-status st-confirmed">{{ $d->type_label }}</span></td>
                    <td class="mono fs-12">{{ $d->order->order_number ?? '-' }}</td>
                    <td class="mono fs-12">{{ $d->container->container_number ?? '-' }}</td>
                    <td class="fs-12 text-dim text-nowrap">{{ $d->created_at->format('d M Y') }}<div>{{ $d->uploader->name ?? '-' }}</div></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.documents.download', $d) }}" class="btn-icon" aria-label="Unduh"><x-icon name="download" /></a>
                        @if ($canWrite)
                        <form method="POST" action="{{ route('admin.documents.destroy', $d) }}" class="d-inline" data-confirm="Hapus dokumen ini?">
                            @csrf @method('DELETE')
                            <button class="btn-icon btn-icon--danger" aria-label="Hapus"><x-icon name="trash" /></button>
                        </form>
                        @endif
                    </td>
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