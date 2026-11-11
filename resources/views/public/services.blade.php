@extends('layouts.base')
@section('title', 'Layanan')
@section('body')
@include('public._nav')
<main class="pub-container" style="padding-top:3.5rem;padding-bottom:3.5rem">
    <span class="eyebrow-sm">Layanan</span>
    <h1 style="font-size:clamp(1.8rem,3.4vw,2.6rem);margin:.6rem 0 1rem;max-width:24ch">Empat pilar layanan logistik.</h1>
    <p class="lead-sm" style="max-width:34rem">Semua layanan tercatat dalam satu sistem sehingga status dan biaya dapat dilacak end-to-end.</p>

    <div class="service-grid mt-4">
        @foreach ([
            ['truck','Container Trucking','Pengambilan dan pengiriman container 20ft/40ft/45hc dari pelabuhan ke lokasi customer. Dispatch per container, monitoring GPS selama perjalanan, dan POD digital.'],
            ['globe','Freight Forwarding','Koordinasi ekspor-impor: booking kapal, dokumen kepabeanan, dan penjadwalan multimoda dengan status terpantau.'],
            ['warehouse','Warehousing','Penyimpanan sementara, stuffing/stripping, dan distribusi dengan pencatatan masuk-keluar barang.'],
            ['ship','Logistics','Perencanaan rute, konsolidasi muatan, laporan operasional, dan analisis biaya pengiriman.'],
        ] as [$icon, $title, $desc])
            <article class="service-card">
                <div class="service-card__icon"><x-icon :name="$icon" size="22" /></div>
                <h3>{{ $title }}</h3><p>{{ $desc }}</p>
                <a href="{{ route('public.contact') }}" class="link-arrow">Minta penawaran <x-icon name="arrow" size="14" /></a>
            </article>
        @endforeach
    </div>

    <div class="cta-band mt-5">
        <div><h2>Butuh penawaran khusus?</h2><p>Ceritakan kebutuhan pengiriman Anda, kami susun estimasi biaya.</p></div>
        <a href="{{ route('public.contact') }}" class="btn btn-primary btn-lg">Request a Quote</a>
    </div>
</main>
@include('public._footer')
@endsection