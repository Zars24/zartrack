@extends('layouts.base')
@section('title', 'Profil')
@section('body')
<div class="page-narrow">
    <a href="{{ auth()->user()->homeRoute() }}" class="back-link"><x-icon name="arrow" size="13" />Kembali ke dashboard</a>

    <div class="page-head mb-3">
        <div class="page-head__text">
            <span class="page-eyebrow">Akun</span>
            <h1>Profil Saya</h1>
            <p>Kelola data akun dan keamanan.</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card__body d-flex align-items-center gap-3">
            <span class="avatar avatar-lg">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
            <div class="min-w-0 flex-grow-1">
                <div class="fw-700 text-truncate">{{ auth()->user()->name }}</div>
                <div class="fs-12 text-dim text-truncate">{{ auth()->user()->email }}</div>
            </div>
            <span class="badge-status st-confirmed">{{ auth()->user()->role_label }}</span>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card__head"><h3>Data Pribadi</h3></div>
        <form method="POST" action="{{ route('profile.update') }}" class="card__body">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="p-name">Nama</label><input id="p-name" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required></div>
                <div class="col-md-6"><label class="form-label" for="p-email">Email</label><input id="p-email" type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required></div>
                <div class="col-12"><label class="form-label" for="p-phone">Telepon</label><input id="p-phone" name="phone" class="form-control" value="{{ old('phone', auth()->user()->phone) }}"></div>
            </div>
            <button class="btn btn-primary mt-3"><x-icon name="check" size="15" />Simpan Profil</button>
        </form>
    </div>

    <div class="card mb-3">
        <div class="card__head"><h3>Ubah Password</h3></div>
        <form method="POST" action="{{ route('profile.password') }}" class="card__body">
            @csrf @method('PUT')
            <div class="mb-3"><label class="form-label" for="cur">Password saat ini</label><input id="cur" type="password" name="current_password" class="form-control" required autocomplete="current-password"></div>
            <div class="mb-3"><label class="form-label" for="new">Password baru</label><input id="new" type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password"></div>
            <div class="mb-3"><label class="form-label" for="conf">Konfirmasi password baru</label><input id="conf" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password"></div>
            <button class="btn btn-primary"><x-icon name="shield" size="15" />Ubah Password</button>
        </form>
    </div>

    <div class="card">
        <div class="card__body d-flex justify-content-between align-items-center gap-3">
            <div><strong class="fs-13">Keluar dari akun</strong><div class="fs-12 text-dim">Sesi akan diakhiri di perangkat ini.</div></div>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-secondary btn-sm">Logout</button></form>
        </div>
    </div>
</div>
@endsection