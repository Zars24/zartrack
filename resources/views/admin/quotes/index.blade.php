@extends('layouts.admin')
@section('title', 'Request Quote')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Sistem</span>
        <h1>Request Quote</h1>
        <p>Permintaan penawaran dari website publik.</p>
    </div>
</div>
<div class="card">
    <div class="card__head">
        <form class="toolbar" method="GET">
            <span class="stat-label">Filter status</span>
            <select name="status" class="form-select" style="max-width:190px" aria-label="Filter status">
                <option value="">Semua status</option>
                @foreach (['new' => 'New', 'contacted' => 'Contacted', 'quoted' => 'Quoted', 'won' => 'Won', 'lost' => 'Lost'] as $k => $v)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary">Filter</button>
            @if (request('status'))
                <a href="{{ route('admin.quotes.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        @if ($quotes->isEmpty())
            <div class="empty-state"><x-icon name="quote" /><h3>Belum ada request</h3><p class="mb-0">Permintaan dari form kontak akan muncul di sini.</p></div>
        @else
        <table class="table">
            <thead><tr><th>Nama</th><th>Kontak</th><th>Layanan</th><th>Rute</th><th>Status</th><th class="text-end">Tanggal</th></tr></thead>
            <tbody>
            @foreach ($quotes as $q)
                <tr>
                    <td class="fs-13 fw-600">{{ $q->name }}<div class="fs-12 text-dim fw-500">{{ $q->company }}</div></td>
                    <td class="fs-12">{{ $q->email }}<div class="text-dim">{{ $q->phone }}</div></td>
                    <td class="fs-12">{{ config('zartrack.service_types.'.$q->service_type, $q->service_type) }}</td>
                    <td class="fs-12 text-muted-2">{{ $q->origin }} → {{ $q->destination }}<div class="text-dim">{{ Str::limit($q->cargo_details, 40) }}</div></td>
                    <td>
                        <form method="POST" action="{{ route('admin.quotes.update', $q) }}">
                            @csrf @method('PATCH')
                            <select name="status" class="form-select form-select-sm" style="width:128px" onchange="this.form.submit()" aria-label="Ubah status quote">
                                @foreach (['new','contacted','quoted','won','lost'] as $s)<option value="{{ $s }}" @selected($q->status===$s)>{{ ucfirst($s) }}</option>@endforeach
                            </select>
                        </form>
                    </td>
                    <td class="fs-12 text-dim text-end text-nowrap">{{ $q->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @if ($quotes->hasPages())
        <div class="card__foot">{{ $quotes->links() }}</div>
    @endif
</div>
@endsection