@extends('layouts.admin')
@section('title', 'User & Akses')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Sistem</span>
        <h1>User &amp; Akses</h1>
        <p>Akun admin, driver, dan customer portal.</p>
    </div>
    <div class="page-head__actions"><a href="{{ route('admin.users.create') }}" class="btn btn-primary"><x-icon name="plus" size="16" />User</a></div>
</div>
<div class="card">
    <div class="card__head">
        <form class="toolbar" method="GET">
            <div class="field-search toolbar__grow" style="max-width:250px">
                <x-icon name="search" size="15" />
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama / email" aria-label="Cari user">
            </div>
            <select name="role" class="form-select" style="max-width:190px" aria-label="Filter role">
                <option value="">Semua role</option>
                @foreach (config('zartrack.roles') as $k => $v)<option value="{{ $k }}" @selected(request('role') === $k)>{{ $v }}</option>@endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary">Filter</button>
            @if (request('q') || request('role'))
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-ghost">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        @if ($users->isEmpty())
            <div class="empty-state"><x-icon name="shield" /><h3>Belum ada user</h3></div>
        @else
        <table class="table">
            <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Customer</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @foreach ($users as $u)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm">{{ strtoupper(substr($u->name, 0, 2)) }}</span>
                            <span class="fw-600 fs-13">{{ $u->name }}</span>
                        </div>
                    </td>
                    <td class="fs-12 text-muted-2">{{ $u->email }}</td>
                    <td><span class="badge-status st-confirmed">{{ $u->role_label }}</span></td>
                    <td class="fs-12 text-dim">{{ $u->customer->name ?? '-' }}</td>
                    <td><span class="badge-status {{ $u->is_active ? 'st-completed' : 'st-cancelled' }}">{{ $u->is_active ? 'aktif' : 'nonaktif' }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.users.edit', $u) }}" class="btn-icon" aria-label="Edit user"><x-icon name="edit" /></a>
                        @if ($u->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="d-inline" data-confirm="Hapus user ini?">
                            @csrf @method('DELETE')
                            <button class="btn-icon btn-icon--danger" aria-label="Hapus user"><x-icon name="trash" /></button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @if ($users->hasPages())
        <div class="card__foot">{{ $users->links() }}</div>
    @endif
</div>
@endsection