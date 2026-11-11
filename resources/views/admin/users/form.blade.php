@extends('layouts.admin')
@section('title', $user->exists ? 'Edit User' : 'User Baru')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <a href="{{ route('admin.users.index') }}" class="back-link"><x-icon name="arrow" size="13" />User &amp; Akses</a>
        <h1>{{ $user->exists ? 'Edit User' : 'User Baru' }}</h1>
        <p>Role menentukan menu dan akses data.</p>
    </div>
</div>
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
    @csrf @if ($user->exists) @method('PUT') @endif
    <div class="card">
        <div class="card__body">
            <div class="form-section">
                <div class="form-section__title">Identitas</div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="name">Nama *</label><input id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
                    <div class="col-md-6"><label class="form-label" for="email">Email *</label><input id="email" type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
                    <div class="col-md-6"><label class="form-label" for="phone">Telepon</label><input id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}"></div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section__title">Akses</div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="role">Role *</label>
                        <select id="role" name="role" class="form-select">
                            @foreach (config('zartrack.roles') as $k => $v)
                                <option value="{{ $k }}" @selected(old('role', $user->role)===$k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label" for="customer_id">Customer (untuk role customer)</label>
                        <select id="customer_id" name="customer_id" class="form-select">
                            <option value="">—</option>
                            @foreach ($customers as $c)<option value="{{ $c->id }}" @selected(old('customer_id', $user->customer_id)==$c->id)>{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section__title">Keamanan</div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="password">Password {{ $user->exists ? '(kosongkan jika tetap)' : '*' }}</label><input id="password" type="password" name="password" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label" for="password_confirmation">Konfirmasi password</label><input id="password_confirmation" type="password" name="password_confirmation" class="form-control"></div>
                    @if ($user->exists)
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $user->is_active))>
                                <label class="form-check-label" for="is_active">Akun aktif</label>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="card__foot d-flex gap-2">
            <button class="btn btn-primary"><x-icon name="check" size="15" />Simpan</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
@endsection