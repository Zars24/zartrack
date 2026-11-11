@extends('layouts.driver')
@section('title', $container->container_number)

@section('content')
<a href="{{ route('driver.dashboard') }}" class="back-link"><x-icon name="arrow" size="13" />Tugas</a>

<div class="card mb-3">
    <div class="card__body">
        <div class="d-flex align-items-start gap-2 mb-2">
            <div>
                <h1 class="mono mb-0" style="font-size:1.1rem">{{ $container->container_number }}</h1>
                <span class="fs-12 text-dim">{{ $container->size }} · {{ number_format($container->weight, 0, ',', '.') }} kg</span>
            </div>
            <x-status :status="$container->status" class="ms-auto" />
        </div>
        <div class="divider"></div>
        <div class="d-grid gap-2 fs-13">
            <div class="d-flex justify-content-between gap-3"><span class="text-dim">Origin</span><strong class="text-end">{{ $container->order->origin }}</strong></div>
            <div class="d-flex justify-content-between gap-3"><span class="text-dim">Tujuan</span><strong class="text-end">{{ $container->order->destination }}</strong></div>
            <div class="d-flex justify-content-between gap-3"><span class="text-dim">Truck</span><strong class="mono">{{ $container->truck->plate_number ?? '—' }}</strong></div>
            <div class="d-flex justify-content-between gap-3"><span class="text-dim">Seal</span><strong class="mono">{{ $container->seal_number ?? '—' }}</strong></div>
        </div>
        <a href="{{ route('public.track.ref', $container->tracking_ref) }}" class="btn btn-sm btn-outline-secondary w-100 mt-3" target="_blank" rel="noopener">Lihat halaman tracking publik</a>
    </div>
</div>

{{-- Status update --}}
@php $allowed = config('zartrack.transitions.'.$container->status, []); @endphp
@if ($allowed)
<div class="card mb-3">
    <div class="card__body">
        <div class="form-section__title">Update Status</div>
        <form method="POST" action="{{ route('driver.tasks.status', $container) }}" id="statusForm">
            @csrf
            <select name="status" class="form-select mb-2" required aria-label="Status baru">
                @foreach ($allowed as $s)
                    <option value="{{ $s }}">{{ config('zartrack.status_labels.'.$s) }}</option>
                @endforeach
            </select>
            <textarea name="note" class="form-control mb-2" rows="2" placeholder="Catatan (opsional)"></textarea>
            <input type="hidden" name="lat" id="lat"><input type="hidden" name="lng" id="lng">
            <button class="btn btn-primary w-100">Kirim Update</button>
        </form>
    </div>
</div>
@endif

{{-- Live GPS --}}
@if (in_array($container->status, ['pickup', 'in_transit']))
<div class="card mb-3">
    <div class="card__body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="form-section__title mb-0">Live GPS</div>
            <span class="badge-status st-in_transit" id="gpsStatus">siaga</span>
        </div>
        <p class="fs-12 text-dim">Bagikan posisi selama perjalanan. GPS berhenti otomatis saat status selesai.</p>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary flex-grow-1" id="gpsStart">Mulai Bagikan</button>
            <button type="button" class="btn btn-outline-secondary" id="gpsStop" disabled>Stop</button>
        </div>
        <div class="fs-12 text-dim mt-2 mono" id="gpsInfo">—</div>
    </div>
</div>
@endif

{{-- POD upload --}}
<div class="card mb-3">
    <div class="card__body">
        <div class="form-section__title">Upload Dokumen / POD</div>
        <form method="POST" action="{{ route('driver.tasks.documents', $container) }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-2">
                <div class="col-6">
                    <select name="type" class="form-select form-select-sm" aria-label="Tipe dokumen">
                        <option value="pod">Proof of Delivery</option>
                        <option value="surat_jalan">Surat Jalan</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div class="col-6"><input name="title" class="form-control form-control-sm" placeholder="Judul dokumen" required></div>
                <div class="col-12"><input type="file" name="file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required></div>
                <div class="col-12"><button class="btn btn-sm btn-outline-secondary w-100">Upload</button></div>
            </div>
        </form>
        @if ($container->documents->isNotEmpty())
            <div class="divider"></div>
            @foreach ($container->documents as $d)
                <div class="d-flex align-items-center gap-2 fs-12 py-1"><x-icon name="doc" size="14" class="text-dim" /><span class="flex-grow-1 text-truncate">{{ $d->title }}</span><span class="text-dim">{{ $d->type_label }}</span></div>
            @endforeach
        @endif
    </div>
</div>

{{-- Issue report --}}
<div class="card mb-3">
    <div class="card__body">
        <div class="form-section__title">Laporkan Kendala</div>
        <form method="POST" action="{{ route('driver.tasks.issue', $container) }}" data-confirm="Laporkan kendala dan ubah status ke Exception?">
            @csrf
            <textarea name="note" class="form-control form-control-sm mb-2" rows="2" placeholder="Contoh: ban pecah di KM 32" required></textarea>
            <button class="btn btn-sm btn-outline-secondary w-100" style="color:var(--danger);border-color:var(--danger)"><x-icon name="alert" size="14" />Laporkan Kendala</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card__body">
        <div class="form-section__title">Riwayat</div>
        <ul class="timeline">
            @forelse ($container->timelines as $i => $tl)
                <li class="{{ $i === 0 ? 'current' : 'done' }}">
                    <div class="tl-time">{{ $tl->created_at->format('d M H:i') }}</div>
                    <div class="tl-title">{{ $tl->status_label }}</div>
                    @if ($tl->note)<div class="tl-note">{{ $tl->note }}</div>@endif
                </li>
            @empty
                <li><span class="tl-note">Belum ada riwayat.</span></li>
            @endforelse
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const pingUrl = @json(route('driver.tasks.ping', $container));
    let watchId = null, timer = null;

    const startBtn = document.getElementById('gpsStart');
    const stopBtn = document.getElementById('gpsStop');
    const statusEl = document.getElementById('gpsStatus');
    const infoEl = document.getElementById('gpsInfo');

    // Attach current position to status update if available
    document.getElementById('statusForm')?.addEventListener('submit', () => {
        if (window.__lastPos) {
            document.getElementById('lat').value = window.__lastPos.lat;
            document.getElementById('lng').value = window.__lastPos.lng;
        }
    });

    const send = (pos) => {
        window.__lastPos = { lat: pos.coords.latitude, lng: pos.coords.longitude };
        infoEl.textContent = `${pos.coords.latitude.toFixed(5)}, ${pos.coords.longitude.toFixed(5)} · akurasi ${Math.round(pos.coords.accuracy)}m`;
        fetch(pingUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({
                lat: pos.coords.latitude,
                lng: pos.coords.longitude,
                speed: pos.coords.speed ? Math.round(pos.coords.speed * 3.6) : null,
                heading: pos.coords.heading
            })
        }).then(r => { statusEl.textContent = r.ok ? 'terkirim' : 'gagal'; })
          .catch(() => { statusEl.textContent = 'offline'; });
    };

    startBtn?.addEventListener('click', () => {
        if (!navigator.geolocation) { infoEl.textContent = 'Geolocation tidak didukung.'; return; }
        watchId = navigator.geolocation.watchPosition(send, (err) => {
            infoEl.textContent = 'GPS error: ' + err.message;
        }, { enableHighAccuracy: true, maximumAge: 15000, timeout: 20000 });
        timer = setInterval(() => {
            navigator.geolocation.getCurrentPosition(send, () => {});
        }, 30000);
        startBtn.disabled = true; stopBtn.disabled = false;
        statusEl.textContent = 'aktif';
    });

    stopBtn?.addEventListener('click', () => {
        if (watchId !== null) navigator.geolocation.clearWatch(watchId);
        clearInterval(timer);
        startBtn.disabled = false; stopBtn.disabled = true;
        statusEl.textContent = 'berhenti';
    });
});
</script>
@endpush