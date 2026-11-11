@extends('layouts.base')
@section('title', 'Tentang Kami')
@section('body')
@include('public._nav')
<main class="pub-container" style="padding-top:3.5rem;padding-bottom:3.5rem">
    <span class="eyebrow-sm">Tentang Kami</span>
    <h1 style="font-size:clamp(1.8rem,3.4vw,2.6rem);margin:.6rem 0 1rem;max-width:30ch">Kami menghubungkan pelabuhan, jalan, dan gudang dalam satu alur yang tertelusur.</h1>
    <p class="lead-sm" style="max-width:38rem;font-size:1rem">ZarTrack dibangun dari kebutuhan nyata operasional container trucking: order multi-container, penugasan driver-armada, status perjalanan individual, sampai penagihan. Semua tercatat.</p>

    <div class="service-grid mt-4">
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
</main>
@include('public._footer')
@endsection