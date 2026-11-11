@extends('layouts.admin')
@section('title', 'Upload Dokumen')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <a href="{{ route('admin.documents.index') }}" class="back-link"><x-icon name="arrow" size="13" />Dokumen</a>
        <h1>Upload Dokumen</h1>
        <p>Lampirkan dokumen ke order atau container.</p>
    </div>
</div>
<form method="POST" action="{{ route('admin.documents.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="card">
        <div class="card__body">
            <div class="form-section">
                <div class="form-section__title">Detail Dokumen</div>
                <div class="row g-3">
                    <div class="col-md-7"><label class="form-label" for="title">Judul *</label><input id="title" name="title" class="form-control" value="{{ old('title') }}" required></div>
                    <div class="col-md-5"><label class="form-label" for="type">Tipe *</label>
                        <select id="type" name="type" class="form-select">
                            @foreach (config('zartrack.document_types') as $k => $v)
                                <option value="{{ $k }}" @selected(old('type') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section__title">Relasi</div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="order_id">Order</label>
                        <select id="order_id" name="order_id" class="form-select">
                            <option value="">—</option>
                            @foreach ($orders as $o)
                                <option value="{{ $o->id }}" @selected(old('order_id') == $o->id)>{{ $o->order_number }} — {{ $o->customer->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label" for="container_id">Container</label>
                        <select id="container_id" name="container_id" class="form-select">
                            <option value="">—</option>
                            @foreach ($containers as $c)
                                <option value="{{ $c->id }}" @selected(old('container_id') == $c->id)>{{ $c->container_number }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section__title">File & Catatan</div>
                <div class="mb-3">
                    <label class="form-label" for="file">File *</label>
                    <input id="file" type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                    <div class="form-text">PDF, JPG, PNG, DOC/DOCX. Maks 10 MB.</div>
                </div>
                <label class="form-label" for="notes">Catatan</label>
                <textarea id="notes" name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="card__foot d-flex gap-2">
            <button class="btn btn-primary"><x-icon name="check" size="15" />Upload</button>
            <a href="{{ route('admin.documents.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
@endsection