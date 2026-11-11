@extends('layouts.base')
@section('title', 'Lacak Kiriman')
@section('body')
@include('public._nav')
<main class="pub-container" style="padding-top:3.5rem;padding-bottom:3.5rem;max-width:900px">
    <span class="eyebrow-sm">Public Tracking</span>
    <h1 style="font-size:clamp(1.7rem,3vw,2.3rem);margin:.6rem 0 1rem">Lacak container Anda.</h1>
    <p class="lead-sm">Masukkan tracking reference yang tertera pada dokumen pengiriman.</p>

    <form method="GET" action="{{ route('public.track') }}" class="d-flex gap-2 mt-3" style="max-width:480px">
        <input name="ref" value="{{ $ref }}" class="form-control mono" placeholder="ZTXXXXXXXXXX" required aria-label="Tracking reference">
        <button class="btn btn-primary">Lacak</button>
    </form>

    @if ($ref && ! $container)
        <div class="card mt-4"><div class="card__body text-center py-4">
            <x-icon name="search" size="32" class="text-dim mb-2" />
            <h3 class="fs-13">Tracking reference tidak ditemukan</h3>
            <p class="fs-13 text-dim mb-0">Periksa kembali kode <span class="mono">{{ $ref }}</span>.</p>
        </div></div>
    @endif

    @if ($container)
        <div class="card mt-4">
            <div class="card__head">
                <h3 class="mono">{{ $container->container_number }}</h3>
                <x-status :status="$container->status" class="ms-auto" />
            </div>
            <div class="card__body">
                <div class="row g-3 fs-13 mb-3">
                    <div class="col-sm-6"><span class="stat-label d-block">Rute</span><strong>{{ $container->order->origin }} → {{ $container->order->destination }}</strong></div>
                    <div class="col-sm-6"><span class="stat-label d-block">Customer</span><strong>{{ $container->order->customer->name }}</strong></div>
                    <div class="col-sm-6"><span class="stat-label d-block">Order</span><strong class="mono">{{ $container->order->order_number }}</strong></div>
                    <div class="col-sm-6"><span class="stat-label d-block">Ukuran</span><strong>{{ $container->size }}</strong></div>
                </div>

                <div id="tracking-map" class="map-frame map-frame--inset mb-3" style="height:280px"></div>

                <div class="form-section__title">Riwayat Status</div>
                <ul class="timeline">
                    @foreach ($container->timelines as $i => $tl)
                        <li class="{{ $i === 0 ? 'current' : 'done' }}">
                            <div class="tl-time">{{ $tl->created_at->format('d M Y H:i') }}</div>
                            <div class="tl-title">{{ $tl->status_label }}</div>
                            @if ($tl->note)<div class="tl-note">{{ $tl->note }}</div>@endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</main>
@include('public._footer')
@endsection

@push('scripts')
@if ($container)
@php
    $trackingLoc = $container->latestLocation;
    $trackingHistory = $container->timelines
        ->filter(fn ($t) => $t->lat && $t->lng)
        ->map(fn ($t) => ['lat' => (float) $t->lat, 'lng' => (float) $t->lng])
        ->values();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('tracking-map');
    if (!el || !window.L) return;
    const loc = @json($trackingLoc);
    const history = @json($trackingHistory);
    const map = L.map(el).setView([-6.2, 106.8], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
    const pts = history.slice();
    if (loc) pts.unshift([parseFloat(loc.lat), parseFloat(loc.lng)]);
    if (pts.length) {
        if (pts.length > 1) L.polyline(pts, { color: '#3056f5', weight: 3 }).addTo(map);
        L.marker(pts[0]).addTo(map).bindPopup('Posisi terakhir').openPopup();
        map.fitBounds(pts.length > 1 ? pts : [[pts[0][0], pts[0][1]], [pts[0][0] + .01, pts[0][1] + .01]], { padding: [30, 30] });
    }
});
</script>
@endif
@endpush