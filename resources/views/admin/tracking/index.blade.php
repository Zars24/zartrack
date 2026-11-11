@extends('layouts.admin')
@section('title', 'Tracking')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Operasional</span>
        <h1>Tracking</h1>
        <p>Posisi container dan status perjalanan.</p>
    </div>
    <div class="page-head__actions">
        <span class="chip"><span class="topbar__dot"></span>{{ $live->count() }} live</span>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card__head">
                <h3>Peta Langsung</h3>
                <span class="ms-auto fs-12 text-dim">{{ $live->count() }} container aktif</span>
            </div>
            <div class="map-frame" style="height:440px">
                <div id="live-map" style="height:100%"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100 d-flex flex-column">
            <div class="card__head"><h3>Container Berjalan</h3></div>
            <div class="card__body p-0 flex-grow-1" style="max-height:440px;overflow-y:auto">
                @forelse ($live as $c)
                    <a href="{{ route('admin.tracking.show', $c) }}" class="list-row" data-jump="{{ $c->latestLocation->lat }},{{ $c->latestLocation->lng }}">
                        <span class="stat-icon" style="width:32px;height:32px;border-radius:9px"><x-icon name="container" size="15" /></span>
                        <span class="min-w-0 flex-grow-1">
                            <span class="d-block mono fs-12 fw-600">{{ $c->container_number }}</span>
                            <span class="d-block fs-12 text-dim text-truncate">{{ $c->driver->name ?? '—' }} · {{ $c->latestLocation->recorded_at->diffForHumans() }}</span>
                        </span>
                        <x-status :status="$c->status" />
                    </a>
                @empty
                    <div class="empty-state py-4"><x-icon name="map" /><p class="fs-13 mb-0">Belum ada posisi GPS.</p></div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card__head">
        <form class="toolbar" method="GET">
            <div class="field-search toolbar__grow" style="max-width:240px">
                <x-icon name="search" size="15" />
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari container / ref" aria-label="Cari container">
            </div>
            <select name="status" class="form-select" style="max-width:180px" aria-label="Filter status">
                <option value="">Semua status</option>
                @foreach (config('zartrack.status_labels') as $k => $v)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary">Filter</button>
        </form>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Container</th><th>Customer</th><th>Driver</th><th>Status</th><th>Posisi terakhir</th><th></th></tr></thead>
            <tbody>
            @forelse ($containers as $c)
                <tr>
                    <td class="mono fs-12 fw-600">{{ $c->container_number }}</td>
                    <td class="fs-12 text-muted-2">{{ $c->order->customer->name ?? '-' }}</td>
                    <td class="fs-13">{{ $c->driver->name ?? '—' }}</td>
                    <td><x-status :status="$c->status" /></td>
                    <td class="fs-12 text-dim">{{ $c->latestLocation?->recorded_at?->diffForHumans() ?? 'Belum ada' }}</td>
                    <td class="text-end"><a href="{{ route('admin.tracking.show', $c) }}" class="btn-icon" aria-label="Lihat tracking"><x-icon name="eye" /></a></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state"><x-icon name="map" /><h3>Tidak ada container</h3></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($containers->hasPages())
        <div class="card__foot">{{ $containers->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
@php
    $markers = $live->map(fn ($c) => [
        'lat' => (float) $c->latestLocation->lat,
        'lng' => (float) $c->latestLocation->lng,
        'label' => $c->container_number,
        'status' => $c->status,
        'url' => route('admin.tracking.show', $c),
    ])->values();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('live-map');
    if (!el || !window.L) return;
    const map = L.map(el).setView([-6.2, 106.8], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19, attribution: '&copy; OpenStreetMap'
    }).addTo(map);
    const points = [];
    const positions = @json($markers);
    positions.forEach(p => {
        const marker = L.marker([p.lat, p.lng]).addTo(map);
        marker.bindPopup(`<strong>${p.label}</strong><br>${p.status}<br><a href="${p.url}">Detail</a>`);
        points.push([p.lat, p.lng]);
    });
    if (points.length > 1) map.fitBounds(points, { padding: [40, 40] });
    else if (points.length === 1) map.setView(points[0], 13);
    setTimeout(() => map.invalidateSize(), 250);
});
</script>
@endpush