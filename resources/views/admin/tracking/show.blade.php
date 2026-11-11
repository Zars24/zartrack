@extends('layouts.admin')
@section('title', $container->container_number)
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <a href="{{ route('admin.tracking.index') }}" class="back-link"><x-icon name="arrow" size="13" />Tracking</a>
        <h1 class="mono">{{ $container->container_number }}</h1>
        <p>{{ $container->order->customer->name ?? '-' }} · {{ $container->order->origin }} → {{ $container->order->destination }}</p>
    </div>
    <div class="page-head__actions"><x-status :status="$container->status" /></div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card__head">
                <h3>Posisi Terakhir</h3>
                @if ($container->latestLocation)
                    <span class="ms-auto chip mono">{{ $container->latestLocation->recorded_at->format('d M H:i') }}</span>
                @endif
            </div>
            <div class="map-frame" style="height:400px">
                <div id="track-map" style="height:100%"></div>
            </div>
        </div>
        <div class="card">
            <div class="card__head"><h3>Riwayat Posisi</h3><span class="chip">{{ $container->locations->count() }}</span></div>
            <div class="table-wrap" style="max-height:300px;overflow-y:auto">
                @if ($container->locations->isEmpty())
                    <div class="empty-state py-4"><x-icon name="location" /><p class="fs-13 mb-0">Belum ada data GPS.</p></div>
                @else
                <table class="table">
                    <thead><tr><th>Waktu</th><th>Koordinat</th><th class="text-end">Kecepatan</th></tr></thead>
                    <tbody>
                    @foreach ($container->locations as $loc)
                        <tr>
                            <td class="fs-12 mono">{{ $loc->recorded_at->format('d M H:i:s') }}</td>
                            <td class="fs-12 mono text-muted-2">{{ $loc->lat }}, {{ $loc->lng }}</td>
                            <td class="fs-12 text-end">{{ $loc->speed ? $loc->speed.' km/h' : '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card__head"><h3>Detail</h3></div>
            <div class="card__body">
                <div class="kv">
                    @foreach ([
                        'Tracking Ref' => $container->tracking_ref,
                        'Size' => $container->size,
                        'Seal' => $container->seal_number ?? '-',
                        'Berat' => number_format($container->weight, 0, ',', '.').' kg',
                        'Driver' => $container->driver->name ?? '—',
                        'Truck' => $container->truck->plate_number ?? '—',
                        'Pickup' => $container->pickup_at?->format('d M Y H:i') ?? '-',
                        'Delivered' => $container->delivered_at?->format('d M Y H:i') ?? '-',
                    ] as $label => $value)
                        <div class="kv__row"><span class="kv__key">{{ $label }}</span><span class="kv__val mono fs-12">{{ $value }}</span></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card__head"><h3>Timeline</h3></div>
            <div class="card__body">
                @if ($container->timelines->isEmpty())
                    <p class="fs-13 text-dim mb-0">Belum ada riwayat status.</p>
                @else
                <ul class="timeline">
                    @foreach ($container->timelines as $i => $tl)
                        <li class="{{ $i === 0 ? 'current' : 'done' }}">
                            <div class="tl-time">{{ $tl->created_at->format('d M Y H:i') }}</div>
                            <div class="tl-title">{{ $tl->status_label }}</div>
                            @if ($tl->note)<div class="tl-note">{{ $tl->note }}</div>@endif
                            <div class="tl-note">{{ $tl->user->name ?? 'Sistem' }}</div>
                        </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $history = $container->locations->map(fn ($l) => [
        'lat' => (float) $l->lat,
        'lng' => (float) $l->lng,
        'at' => $l->recorded_at->toIso8601String(),
    ])->values();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('track-map');
    if (!el || !window.L) return;
    const history = @json($history);
    const map = L.map(el).setView([-6.2, 106.8], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
    if (history.length) {
        const path = history.map(p => [p.lat, p.lng]);
        L.polyline(path, { color: '#3056f5', weight: 3, opacity: .8 }).addTo(map);
        const last = history[0];
        L.marker([last.lat, last.lng]).addTo(map).bindPopup('Posisi terakhir').openPopup();
        map.fitBounds(path.length > 1 ? path : [[last.lat, last.lng], [last.lat + .005, last.lng + .005]], { padding: [40, 40] });
    }
    setTimeout(() => map.invalidateSize(), 250);
});
</script>
@endpush