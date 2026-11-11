@extends('layouts.admin')
@section('title', 'Customer')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Operasional</span>
        <h1>Customer</h1>
        <p>Data perusahaan pelanggan.</p>
    </div>
    <div class="page-head__actions"><a href="{{ route('admin.customers.create') }}" class="btn btn-primary"><x-icon name="plus" size="16" />Customer</a></div>
</div>

<div class="card">
    <div class="card__head">
        <form class="toolbar" method="GET">
            <div class="field-search toolbar__grow" style="max-width:280px">
                <x-icon name="search" size="15" />
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama / email" aria-label="Cari customer">
            </div>
            <select name="status" class="form-select" style="max-width:160px" aria-label="Filter status">
                <option value="">Semua status</option>
                <option value="active" @selected(request('status')==='active')>Active</option>
                <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary">Filter</button>
            @if (request('q') || request('status'))
                <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        @if ($customers->isEmpty())
            <div class="empty-state"><x-icon name="users" /><h3>Belum ada customer</h3><p class="mb-0">Tambahkan customer untuk mulai membuat order.</p></div>
        @else
        <table class="table">
            <thead><tr><th>Perusahaan</th><th>Kontak</th><th>Kota</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @foreach ($customers as $c)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm">{{ strtoupper(substr($c->name, 0, 2)) }}</span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.customers.show', $c) }}" class="fw-600 text-reset d-block">{{ $c->name }}</a>
                                <div class="fs-12 text-dim">{{ $c->npwp ?: 'NPWP belum diisi' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="fs-13">{{ $c->contact_person ?? '-' }}<div class="fs-12 text-dim">{{ $c->phone ?? $c->email }}</div></td>
                    <td class="fs-13">{{ $c->city ?? '-' }}</td>
                    <td><span class="badge-status {{ $c->status === 'active' ? 'st-completed' : 'st-cancelled' }}">{{ $c->status }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.customers.edit', $c) }}" class="btn-icon" aria-label="Edit customer"><x-icon name="edit" /></a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @if ($customers->hasPages())
        <div class="card__foot">{{ $customers->links() }}</div>
    @endif
</div>
@endsection