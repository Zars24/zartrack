@extends('layouts.base')
@section('title', 'Tentang Kami')
@section('meta_description', 'ZarTrack membangun alur container trucking yang tertelusur: order multi-container, driver dan armada, status, sampai penagihan.')
@section('body')
@include('public._nav')
<main>
    <section class="hero hero--photo hero--page">
        <img class="hero__photo" src="{{ asset('images/photos/container-stack.jpg') }}" alt="Deretan container tersusun di terminal peti kemas" fetchpriority="high">
        <div class="hero__scrim" aria-hidden="true"></div>
        <div class="pub-container hero__inner">
            <div class="hero__copy">
                <span class="eyebrow eyebrow--dark">Tentang Kami</span>
                <h1>Kami menghubungkan pelabuhan, jalan, dan gudang dalam satu alur yang tertelusur.</h1>
                <p class="lead">ZarTrack dibangun dari kebutuhan nyata operasional container trucking: order multi-container, penugasan driver-armada, status perjalanan individual, sampai penagihan. Semua tercatat.</p>
            </div>
        </div>
    </section>

    <div class="pub-page">
        <div class="pub-container">
            <div class="section-head">
                <div><span class="eyebrow-sm">Prinsip Kerja</span><h2>Tiga hal yang tidak kami kompromikan.</h2></div>
                <p>Sistem ini menolak jalan pintas yang membuat data operasional tidak bisa dipercaya.</p>
            </div>

            <div class="service-grid">
                @foreach ([
                    ['shield','Integritas Data','Setiap perubahan status melewati validasi backend dan tersimpan dalam timeline permanen.'],
                    ['clock','Respons Cepat','Monitoring 24/7 dengan pembaruan status dan posisi GPS real-time dari driver.'],
                    ['sparkle','Transparansi','Customer melihat status, dokumen, dan invoice miliknya sendiri tanpa perantara.'],
                ] as [$icon, $title, $desc])
                    <div class="service-card">
                        <div class="service-card__icon"><x-icon :name="$icon" size="22" /></div>
                        <h3>{{ $title }}</h3><p>{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <section class="pub-section pub-section--dark">
        <div class="pub-container feature-split">
            <div class="fleet-media">
                <img src="{{ asset('images/photos/warehouse-operations.jpg') }}" alt="Aktivitas bongkar muat di gudang penyimpanan" loading="lazy" decoding="async">
                <div class="fleet-media__plate">
                    <x-icon name="warehouse" size="16" />
                    <div><strong>Dari pelabuhan ke gudang</strong><small>Satu alur · Satu data</small></div>
                </div>
            </div>
            <div>
                <span class="eyebrow-sm">Cara Kerja</span>
                <h2>Satu sumber data untuk semua pihak.</h2>
                <p class="lead-sm">Operasional, driver, dan customer membaca catatan yang sama. Tidak ada rekap manual yang berbeda versi.</p>
                <ul class="check-list">
                    <li><x-icon name="check" size="16" /> Order dan container punya status yang dihitung sistem</li>
                    <li><x-icon name="check" size="16" /> Driver hanya melihat tugas yang ditugaskan kepadanya</li>
                    <li><x-icon name="check" size="16" /> Customer hanya melihat data perusahaannya sendiri</li>
                    <li><x-icon name="check" size="16" /> Setiap aksi penting masuk activity log</li>
                </ul>
                <a href="{{ route('public.services') }}" class="btn btn-primary mt-4">Lihat Layanan <x-icon name="arrow" size="15" /></a>
            </div>
        </div>
    </section>
</main>
@include('public._footer')
@endsection