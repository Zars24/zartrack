@extends('layouts.base')
@section('title', 'Layanan')
@section('meta_description', 'Container trucking, freight forwarding, warehousing, dan logistik dengan status serta biaya yang dapat dilacak end-to-end.')
@section('body')
@include('public._nav')
<main>
    <section class="hero hero--photo hero--page">
        <img class="hero__photo" src="{{ asset('images/photos/port-cranes.jpg') }}" alt="Crane pelabuhan mengangkat container dari kapal" fetchpriority="high">
        <div class="hero__scrim" aria-hidden="true"></div>
        <div class="pub-container hero__inner">
            <div class="hero__copy">
                <span class="eyebrow eyebrow--dark">Layanan</span>
                <h1>Empat pilar layanan logistik.</h1>
                <p class="lead">Semua layanan tercatat dalam satu sistem sehingga status dan biaya dapat dilacak end-to-end.</p>
            </div>
        </div>
    </section>

    <div class="pub-page">
        <div class="pub-container">
            <div class="cap-grid cap-grid--mosaic">
                @foreach ([
                    ['container-stack.jpg','truck','Container Trucking','Pengambilan dan pengiriman container 20ft/40ft/45hc dari pelabuhan ke lokasi customer. Dispatch per container, monitoring GPS selama perjalanan, dan POD digital.','50% 52%','Deretan container tersusun di terminal peti kemas'],
                    ['port-cranes.jpg','ship','Freight Forwarding','Koordinasi ekspor-impor: booking kapal, dokumen kepabeanan, dan penjadwalan multimoda dengan status terpantau.','50% 42%','Crane pelabuhan mengangkat container dari kapal'],
                    ['warehouse-operations.jpg','warehouse','Warehousing','Penyimpanan sementara, stuffing/stripping, dan distribusi dengan pencatatan masuk-keluar barang.','50% 58%','Aktivitas bongkar muat di gudang penyimpanan'],
                    ['terminal-fleet.jpg','globe','Logistics','Perencanaan rute, konsolidasi muatan, laporan operasional, dan analisis biaya pengiriman.','50% 55%','Armada truk container di terminal'],
                ] as [$img, $icon, $title, $desc, $pos, $alt])
                    <article class="cap-card">
                        <div class="cap-card__media">
                            <img src="{{ asset('images/photos/'.$img) }}" alt="{{ $alt }}" loading="lazy" decoding="async" style="object-position:{{ $pos }}">
                        </div>
                        <div class="cap-card__body">
                            <span class="cap-card__icon"><x-icon :name="$icon" size="18" /></span>
                            <h3>{{ $title }}</h3>
                            <p>{{ $desc }}</p>
                            <a href="{{ route('public.contact') }}" class="link-arrow">Minta penawaran <x-icon name="arrow" size="14" /></a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="cta-band mt-5">
                <div><h2>Butuh penawaran khusus?</h2><p>Ceritakan kebutuhan pengiriman Anda, kami susun estimasi biaya.</p></div>
                <a href="{{ route('public.contact') }}" class="btn btn-primary btn-lg">Request a Quote</a>
            </div>
        </div>
    </div>
</main>
@include('public._footer')
@endsection