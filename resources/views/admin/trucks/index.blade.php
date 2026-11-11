@extends('layouts.admin')
@section('title', 'Armada')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Operasional</span>
        <h1>Armada</h1>
        <p>Truk dan trailer operasional.</p>
    </div>
    <div class="page-head__actions"><a href="{{ route('admin.trucks.create') }}" class="btn btn-primary"><x-icon name="plus" size="16" />Truck</a></div>
</div>
<div class="card">
    <div class="card__head">
        <form class="toolbar" method="GET">
            <div class="field-search toolbar__grow" style="max-width:250px">
                <x-icon name="search" size="15" />
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari plat / tipe" aria-label="Cari armada">
            </div>
            <select name="status" class="form-select" style="max-width:170px" aria-label="Filter status">
                <option value="">Semua status</option>
                @foreach (['available' => 'Available', 'in_use' => 'In Use', 'maintenance' => 'Maintenance'] as $k => $v)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary">Filter</button>
            @if (request('q') || request('status'))
                <a href="{{ route('admin.trucks.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        @if ($trucks->isEmpty())
            <div class="empty-state"><x-icon name="truck" /><h3>Belum ada armada</h3></div>
        @else
        <table class="table">
            <thead><tr><th>Plat</th><th>Tipe</th><th class="text-end">Kapasitas</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @foreach ($trucks as $t)
                <tr>
                    <td><span class="chip mono fw-600">{{ $t->plate_number }}</span></td>
                    <td class="fs-13">{{ $t->type }}<div class="fs-12 text-dim">{{ $t->brand }} {{ $t->model }}</div></td>
                    <td class="fs-13 text-end num">{{ number_format($t->capacity_kg, 0, ',', '.') }} kg</td>
                    <td><span class="badge-status {{ $t->status === 'available' ? 'st-completed' : ($t->status === 'in_use' ? 'st-in_transit' : 'st-on_hold') }}">{{ str_replace('_',' ',$t->status) }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.trucks.edit', $t) }}" class="btn-icon" aria-label="Edit truck"><x-icon name="edit" /></a>
                        <form method="POST" action="{{ route('admin.trucks.destroy', $t) }}" class="d-inline" data-confirm="Hapus truck ini?">
                            @csrf @method('DELETE')
                            <button class="btn-icon btn-icon--danger" aria-label="Hapus truck"><x-icon name="trash" /></button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @if ($trucks->hasPages())
        <div class="card__foot">{{ $trucks->links() }}</div>
    @endif
</div>
@endsection