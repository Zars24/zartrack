@extends('layouts.admin')
@section('title', $driver->exists ? 'Edit Driver' : 'Driver Baru')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <a href="{{ route('admin.drivers.index') }}" class="back-link"><x-icon name="arrow" size="13" />Driver</a>
        <h1>{{ $driver->exists ? 'Edit Driver' : 'Driver Baru' }}</h1>
        <p>Data pribadi, SIM, dan akun portal driver.</p>
    </div>
</div>
<form method="POST" action="{{ $driver->exists ? route('admin.drivers.update', $driver) : route('admin.drivers.store') }}">
    @csrf @if ($driver->exists) @method('PUT') @endif
    <div class="card">
        <div class="card__body">
            <div class="form-section">
                <div class="form-section__title">Data Driver</div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="name">Nama *</label><input id="name" name="name" class="form-control" value="{{ old('name', $driver->name) }}" required></div>
                    <div class="col-md-6"><label class="form-label" for="phone">Telepon</label><input id="phone" name="phone" class="form-control" value="{{ old('phone', $driver->phone) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="license_number">Nomor SIM</label><input id="license_number" name="license_number" class="form-control mono" value="{{ old('license_number', $driver->license_number) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="license_expiry">Masa berlaku SIM</label><input id="license_expiry" type="date" name="license_expiry" class="form-control" value="{{ old('license_expiry', $driver->license_expiry?->format('Y-m-d')) }}"></div>
                    <div class="col-12"><label class="form-label" for="address">Alamat</label><textarea id="address" name="address" class="form-control" rows="2">{{ old('address', $driver->address) }}</textarea></div>
                    <div class="col-md-4"><label class="form-label" for="status">Status *</label>
                        <select id="status" name="status" class="form-select">
                            @foreach (['available' => 'Available', 'on_duty' => 'On Duty', 'inactive' => 'Inactive'] as $k => $v)
                                <option value="{{ $k }}" @selected(old('status', $driver->status)===$k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section__title">Akun Portal Driver</div>
                <p class="fs-12 text-dim">Isi email untuk membuat / menautkan akun login driver.</p>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="email">Email</label><input id="email" type="email" name="email" class="form-control" value="{{ old('email', $driver->user?->email) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="password">Password</label><input id="password" type="password" name="password" class="form-control" placeholder="{{ $driver->user ? 'Kosongkan jika tidak diubah' : 'Default: password' }}"></div>
                </div>
            </div>
        </div>
        <div class="card__foot d-flex gap-2">
            <button class="btn btn-primary"><x-icon name="check" size="15" />Simpan</button>
            <a href="{{ route('admin.drivers.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
@endsection