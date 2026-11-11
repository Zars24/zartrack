@extends('layouts.admin')
@section('title', $customer->exists ? 'Edit Customer' : 'Customer Baru')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <a href="{{ route('admin.customers.index') }}" class="back-link"><x-icon name="arrow" size="13" />Customer</a>
        <h1>{{ $customer->exists ? 'Edit Customer' : 'Customer Baru' }}</h1>
        <p>Data perusahaan, kontak, dan alamat penagihan.</p>
    </div>
</div>
<form method="POST" action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}">
    @csrf @if ($customer->exists) @method('PUT') @endif
    <div class="card">
        <div class="card__body">
            <div class="form-section">
                <div class="form-section__title">Identitas Perusahaan</div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="name">Nama perusahaan *</label><input id="name" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required></div>
                    <div class="col-md-6"><label class="form-label" for="contact_person">Contact person</label><input id="contact_person" name="contact_person" class="form-control" value="{{ old('contact_person', $customer->contact_person) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="email">Email</label><input id="email" type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}"></div>
                    <div class="col-md-6"><label class="form-label" for="phone">Telepon</label><input id="phone" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}"></div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section__title">Alamat & Legal</div>
                <div class="row g-3">
                    <div class="col-12"><label class="form-label" for="address">Alamat</label><textarea id="address" name="address" class="form-control" rows="2">{{ old('address', $customer->address) }}</textarea></div>
                    <div class="col-md-4"><label class="form-label" for="city">Kota</label><input id="city" name="city" class="form-control" value="{{ old('city', $customer->city) }}"></div>
                    <div class="col-md-4"><label class="form-label" for="province">Provinsi</label><input id="province" name="province" class="form-control" value="{{ old('province', $customer->province) }}"></div>
                    <div class="col-md-4"><label class="form-label" for="npwp">NPWP</label><input id="npwp" name="npwp" class="form-control mono" value="{{ old('npwp', $customer->npwp) }}"></div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section__title">Status & Catatan</div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label" for="status">Status *</label>
                        <select id="status" name="status" class="form-select">
                            <option value="active" @selected(old('status',$customer->status)==='active')>Active</option>
                            <option value="inactive" @selected(old('status',$customer->status)==='inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-8"><label class="form-label" for="notes">Catatan</label><textarea id="notes" name="notes" class="form-control" rows="2">{{ old('notes', $customer->notes) }}</textarea></div>
                </div>
            </div>
        </div>
        <div class="card__foot d-flex gap-2">
            <button class="btn btn-primary"><x-icon name="check" size="15" />Simpan</button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
@endsection