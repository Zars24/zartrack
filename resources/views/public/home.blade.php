@extends('layouts.base')
@section('title', 'Container Trucking & Logistik Terintegrasi')
@section('meta_description', 'ZarTrack mengelola pengiriman container, armada, GPS tracking, dokumen, dan invoice dalam satu platform.')

@section('body')
@include('public._nav')

<main>
<section class="hero">
    <div class="hero__bg" aria-hidden="true"></div>
    <div class="pub-container hero__inner">
        <div class="hero__copy reveal">
            <span class="eyebrow"><span class="pulse"></span>Live GPS Tracking · Multi-Container</span>
            <h1>Kendali penuh atas<br><span class="grad">setiap container</span> yang bergerak.</h1>
            <p class="lead">ZarTrack menyatukan dispatch, armada, supir, dokumen, dan invoice dalam satu sistem operasional yang rapi. Pantau pengiriman secara real-time dari pelabuhan sampai gudang tujuan.</p>
            <div class="hero__cta">
                <a href="{{ route('public.contact') }}" class="btn btn-primary btn-lg">Request a Quote</a>
                <a href="{{ route('public.track') }}" class="btn btn-outline-secondary btn-lg">Lacak Kiriman</a>
            </div>
            <div class="hero__stats">
                <div><strong>24/7</strong><span>Monitoring</span></div>
                <div><strong>6</strong><span>Role Terintegrasi</span></div>
                <div><strong>100%</strong><span>Riwayat Terlacak</span></div>
            </div>
        </div>

        <div class="hero__visual reveal">
            <div class="track-card">
                <div class="track-card__head">
                    <span class="badge-status st-in_transit"><span class="dot"></span>In Transit</span>
                    <span class="mono fs-12 text-dim">ZT7F3A9C2D1</span>
                </div>
                <div class="track-card__id">TCLU 412 883 7</div>
                <div class="track-card__route">
                    <div><span class="dot-start"></span><small>Origin</small><strong>Tanjung Priok</strong></div>
                    <div class="route-line"><span class="truck-dot"><x-icon name="truck" size="14" /></span></div>
                    <div class="text-end"><span class="dot-end"></span><small>Destination</small><strong>Cikarang</strong></div>
                </div>
                <div class="track-card__steps">
                    <span class="done">Confirmed</span><span class="done">Assigned</span><span class="done">Pickup</span><span class="active">In Transit</span><span>Delivered</span>
                </div>
                <div class="track-card__foot">
                    <div><small>Driver</small><strong>Budi Santoso</strong></div>
                    <div><small>Armada</small><strong class="mono">B 9021 KTR</strong></div>
                    <div><small>ETA</small><strong>2j 15m</strong></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="pub-section">
    <div class="pub-container">
        <div class="section-head">
            <div><span class="eyebrow-sm">Kapabilitas</span><h2>Satu platform, seluruh rantai logistik.</h2></div>
            <p>Dibangun untuk operasional container trucking dengan alur status yang ketat dan jejak audit lengkap.</p>
        </div>
        <div class="service-grid">
            @foreach ([
                ['truck','Container Trucking','Pengiriman container dari pelabuhan ke gudang tujuan dengan dispatch dan monitoring per container.'],
                ['globe','Freight Forwarding','Koordinasi pengapalan, dokumen ekspor-impor, dan penjadwalan multimoda.'],
                ['warehouse','Warehousing','Penyimpanan, sortir, dan distribusi dengan pencatatan stok yang tertelusur.'],
                ['ship','Logistics','Perencanaan rute, konsolidasi muatan, dan laporan operasional terpadu.'],
            ] as [$icon, $title, $desc])
                <article class="service-card">
                    <div class="service-card__icon"><x-icon :name="$icon" size="22" /></div>
                    <h3>{{ $title }}</h3>
                    <p>{{ $desc }}</p>
                    <a href="{{ route('public.services') }}" class="link-arrow">Pelajari <x-icon name="arrow" size="14" /></a>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="pub-section pub-section--alt">
    <div class="pub-container feature-split">
        <div>
            <span class="eyebrow-sm">Alur Operasional</span>
            <h2>Status yang jelas. Tanpa tebak-tebakan.</h2>
            <p class="lead-sm">Setiap perubahan status divalidasi backend dan tercatat dalam timeline container. Customer dan driver melihat informasi yang sama, real-time.</p>
            <ul class="check-list">
                <li><x-icon name="check" size="16" /> Multi-container per order dengan status individual</li>
                <li><x-icon name="check" size="16" /> Penugasan driver &amp; armada terpisah tiap container</li>
                <li><x-icon name="check" size="16" /> Live GPS hanya aktif selama perjalanan</li>
                <li><x-icon name="check" size="16" /> Invoice PDF dan status pembayaran terhubung order</li>
            </ul>
        </div>
        <div class="flow-card">
            @foreach (['pending','confirmed','assigned','pickup','in_transit','delivered','completed'] as $i => $s)
                <div class="flow-step">
                    <span class="flow-num">{{ $i + 1 }}</span>
                    <div><strong>{{ config('zartrack.status_labels.'.$s) }}</strong><small>{{ ['Order diterima','Dikonfirmasi ops','Driver ditugaskan','Barang diambil','Dalam perjalanan','Terkirim','Selesai'][$i] }}</small></div>
                    <x-status :status="$s" />
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="pub-section">
    <div class="pub-container">
        <div class="cta-band">
            <div>
                <h2>Siap memindahkan operasional ke sistem?</h2>
                <p>Kirim permintaan penawaran, tim kami menghubungi dalam 1×24 jam kerja.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('public.contact') }}" class="btn btn-primary btn-lg">Minta Penawaran</a>
                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg" style="border-color:rgba(255,255,255,.3);color:#fff;background:transparent">Masuk Portal</a>
            </div>
        </div>
    </div>
</section>
</main>

@include('public._footer')
@endsection