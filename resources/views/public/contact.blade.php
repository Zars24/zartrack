@extends('layouts.base')
@section('title', 'Kontak & Quote')
@section('body')
@include('public._nav')
<main class="pub-container" style="padding-top:3.5rem;padding-bottom:3.5rem">
    <div class="row g-4">
        <div class="col-lg-5">
            <span class="eyebrow-sm">Hubungi Kami</span>
            <h1 style="font-size:clamp(1.7rem,3vw,2.3rem);margin:.6rem 0 1rem">Minta penawaran pengiriman.</h1>
            <p class="lead-sm">Isi formulir, tim operasional menghubungi Anda dalam 1×24 jam kerja. Tidak perlu membuat akun.</p>

            <div class="card mt-4">
                <div class="card__body d-grid gap-3">
                    <div class="d-flex gap-3"><div class="stat-icon"><x-icon name="location" /></div><div><strong class="fs-13 d-block">Kantor</strong><span class="fs-13 text-muted-2">Jl. Pelabuhan Raya No. 12, Jakarta Utara</span></div></div>
                    <div class="d-flex gap-3"><div class="stat-icon"><x-icon name="phone" /></div><div><strong class="fs-13 d-block">Telepon</strong><span class="fs-13 text-muted-2">021-555-0199</span></div></div>
                    <div class="d-flex gap-3"><div class="stat-icon"><x-icon name="mail" /></div><div><strong class="fs-13 d-block">Email</strong><span class="fs-13 text-muted-2">sales@zartrack.id</span></div></div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card__head"><h3>Lacak Kiriman</h3></div>
                <div class="card__body">
                    <form action="{{ route('public.track') }}" method="GET" class="d-flex gap-2">
                        <input name="ref" class="form-control mono" placeholder="ZTXXXXXXXXXX" required aria-label="Tracking reference">
                        <button class="btn btn-primary">Cari</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card__head"><h3>Form Permintaan Penawaran</h3></div>
                <form method="POST" action="{{ route('public.quote') }}" class="card__body">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label" for="q-name">Nama *</label><input id="q-name" name="name" class="form-control" value="{{ old('name') }}" required></div>
                        <div class="col-md-6"><label class="form-label" for="q-company">Perusahaan</label><input id="q-company" name="company" class="form-control" value="{{ old('company') }}"></div>
                        <div class="col-md-6"><label class="form-label" for="q-email">Email *</label><input id="q-email" type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
                        <div class="col-md-6"><label class="form-label" for="q-phone">Telepon *</label><input id="q-phone" name="phone" class="form-control" value="{{ old('phone') }}" required></div>
                        <div class="col-md-4"><label class="form-label" for="q-service">Layanan *</label>
                            <select id="q-service" name="service_type" class="form-select" required>
                                @foreach (config('zartrack.service_types') as $k => $v)
                                    <option value="{{ $k }}" @selected(old('service_type') === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label" for="q-origin">Origin *</label><input id="q-origin" name="origin" class="form-control" value="{{ old('origin') }}" required placeholder="Tanjung Priok"></div>
                        <div class="col-md-4"><label class="form-label" for="q-dest">Destination *</label><input id="q-dest" name="destination" class="form-control" value="{{ old('destination') }}" required placeholder="Cikarang"></div>
                        <div class="col-12"><label class="form-label" for="q-cargo">Detail kargo</label><textarea id="q-cargo" name="cargo_details" class="form-control" rows="2" placeholder="Jumlah container, berat, komoditas">{{ old('cargo_details') }}</textarea></div>
                        <div class="col-12"><label class="form-label" for="q-msg">Pesan tambahan</label><textarea id="q-msg" name="message" class="form-control" rows="2">{{ old('message') }}</textarea></div>
                    </div>
                    <button class="btn btn-primary mt-3"><x-icon name="mail" size="15" />Kirim Permintaan</button>
                </form>
            </div>
        </div>
    </div>
</main>
@include('public._footer')
@endsection