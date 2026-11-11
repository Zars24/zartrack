@extends('layouts.admin')
@section('title', $truck->exists ? 'Edit Truck' : 'Truck Baru')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <a href="{{ route('admin.trucks.index') }}" class="back-link"><x-icon name="arrow" size="13" />Armada</a>
        <h1>{{ $truck->exists ? 'Edit Truck' : 'Truck Baru' }}</h1>
        <p>Identitas kendaraan, kapasitas, dan status operasional.</p>
    </div>
</div>
<form method="POST" action="{{ $truck->exists ? route('admin.trucks.update', $truck) : route('admin.trucks.store') }}">
    @csrf @if ($truck->exists) @method('PUT') @endif
    <div class="card">
        <div class="card__body">
            <div class="form-section">
                <div class="form-section__title">Identitas Kendaraan</div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label" for="plate_number">Plat nomor *</label><input id="plate_number" name="plate_number" class="form-control mono" value="{{ old('plate_number', $truck->plate_number) }}" required></div>
                    <div class="col-md-4"><label class="form-label" for="type">Tipe *</label><input id="type" name="type" class="form-control" value="{{ old('type', $truck->type) }}" required placeholder="trailer / tronton"></div>
                    <div class="col-md-4"><label class="form-label" for="capacity_kg">Kapasitas (kg) *</label><input id="capacity_kg" type="number" name="capacity_kg" class="form-control" value="{{ old('capacity_kg', $truck->capacity_kg ?? 0) }}" min="0" required></div>
                    <div class="col-md-4"><label class="form-label" for="brand">Merek</label><input id="brand" name="brand" class="form-control" value="{{ old('brand', $truck->brand) }}"></div>
                    <div class="col-md-4"><label class="form-label" for="model">Model</label><input id="model" name="model" class="form-control" value="{{ old('model', $truck->model) }}"></div>
                    <div class="col-md-4"><label class="form-label" for="status">Status *</label>
                        <select id="status" name="status" class="form-select">
                            @foreach (['available' => 'Available', 'in_use' => 'In Use', 'maintenance' => 'Maintenance'] as $k => $v)
                                <option value="{{ $k }}" @selected(old('status', $truck->status)===$k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section__title">Catatan</div>
                <label class="form-label visually-hidden" for="notes">Catatan</label>
                <textarea id="notes" name="notes" class="form-control" rows="2">{{ old('notes', $truck->notes) }}</textarea>
            </div>
        </div>
        <div class="card__foot d-flex gap-2">
            <button class="btn btn-primary"><x-icon name="check" size="15" />Simpan</button>
            <a href="{{ route('admin.trucks.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
@endsection