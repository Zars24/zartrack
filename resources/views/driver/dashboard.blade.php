@extends('layouts.driver')
@section('title', 'Tugas Saya')

@section('content')
<div class="card mb-3">
    <div class="card__body">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <div>
                <div class="stat-label">Status Anda</div>
                <strong class="fs-13">{{ ucfirst(str_replace('_', ' ', $driver->status)) }}</strong>
            </div>
            <span class="badge-status {{ $driver->status === 'available' ? 'st-completed' : 'st-in_transit' }}">{{ $driver->status === 'available' ? 'Siap bertugas' : 'Sedang bertugas' }}</span>
        </div>
        <div class="d-flex gap-3 fs-12 text-dim">
            <span><strong class="text-reset">{{ $tasks->count() }}</strong> tugas aktif</span>
            <span><strong class="text-reset">{{ $done }}</strong>/{{ $total }} selesai</span>
        </div>
    </div>
</div>

<div class="form-section__title">Tugas Aktif</div>

@forelse ($tasks as $t)
    <a href="{{ route('driver.tasks.show', $t) }}" class="driver-task">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="mono fw-600 fs-13">{{ $t->container_number }}</span>
            <x-status :status="$t->status" class="ms-auto" />
        </div>
        <div class="fs-12 text-muted-2 mb-2">{{ $t->order->origin }} → {{ $t->order->destination }}</div>
        <div class="d-flex flex-wrap gap-3 fs-12 text-dim">
            <span class="d-inline-flex align-items-center gap-1"><x-icon name="truck" size="12" /> {{ $t->truck->plate_number ?? '—' }}</span>
            <span class="d-inline-flex align-items-center gap-1"><x-icon name="users" size="12" /> {{ $t->order->customer->name ?? '—' }}</span>
        </div>
    </a>
@empty
    <div class="card"><div class="empty-state"><x-icon name="check" size="32" /><h3>Belum ada tugas</h3><p class="fs-13 mb-0">Tugas baru akan muncul setelah dispatcher menugaskan.</p></div></div>
@endforelse
@endsection