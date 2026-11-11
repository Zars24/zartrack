@extends('layouts.admin')
@section('title', 'Driver')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Operasional</span>
        <h1>Driver</h1>
        <p>Data supir dan akun portal driver.</p>
    </div>
    <div class="page-head__actions"><a href="{{ route('admin.drivers.create') }}" class="btn btn-primary"><x-icon name="plus" size="16" />Driver</a></div>
</div>
<div class="card">
    <div class="card__head">
        <form class="toolbar" method="GET">
            <div class="field-search toolbar__grow" style="max-width:260px">
                <x-icon name="search" size="15" />
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama / telepon" aria-label="Cari driver">
            </div>
            <select name="status" class="form-select" style="max-width:170px" aria-label="Filter status">
                <option value="">Semua status</option>
                @foreach (['available' => 'Available', 'on_duty' => 'On Duty', 'inactive' => 'Inactive'] as $k => $v)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary">Filter</button>
            @if (request('q') || request('status'))
                <a href="{{ route('admin.drivers.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        @if ($drivers->isEmpty())
            <div class="empty-state"><x-icon name="driver" /><h3>Belum ada driver</h3></div>
        @else
        <table class="table">
            <thead><tr><th>Nama</th><th>Kontak</th><th>SIM</th><th>Akun</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @foreach ($drivers as $d)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm">{{ strtoupper(substr($d->name, 0, 2)) }}</span>
                            <span class="fw-600 fs-13">{{ $d->name }}</span>
                        </div>
                    </td>
                    <td class="fs-13">{{ $d->phone ?? '-' }}</td>
                    <td class="fs-12 mono">{{ $d->license_number ?? '-' }}<div class="text-dim">{{ $d->license_expiry?->format('d M Y') }}</div></td>
                    <td class="fs-12 text-muted-2">{{ $d->user?->email ?? '—' }}</td>
                    <td><span class="badge-status {{ $d->status === 'available' ? 'st-completed' : ($d->status === 'on_duty' ? 'st-in_transit' : 'st-cancelled') }}">{{ str_replace('_',' ',$d->status) }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.drivers.edit', $d) }}" class="btn-icon" aria-label="Edit driver"><x-icon name="edit" /></a>
                        <form method="POST" action="{{ route('admin.drivers.destroy', $d) }}" class="d-inline" data-confirm="Hapus driver ini?">
                            @csrf @method('DELETE')
                            <button class="btn-icon btn-icon--danger" aria-label="Hapus driver"><x-icon name="trash" /></button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @if ($drivers->hasPages())
        <div class="card__foot">{{ $drivers->links() }}</div>
    @endif
</div>
@endsection