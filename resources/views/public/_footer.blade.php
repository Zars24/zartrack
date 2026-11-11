<footer class="pub-footer">
    <div class="pub-container">
        <div class="pub-footer__grid">
            <div>
                <a href="{{ route('public.home') }}" class="brand mb-2">
                    <span class="brand-mark">ZT</span><span class="brand-text">Zar<span>Track</span></span>
                </a>
                <p class="fs-13 text-dim" style="max-width:280px">Container trucking &amp; logistics management system. Dibangun untuk operasional nyata.</p>
            </div>
            <div>
                <h4>Perusahaan</h4>
                <a href="{{ route('public.about') }}">Tentang Kami</a>
                <a href="{{ route('public.services') }}">Layanan</a>
                <a href="{{ route('public.contact') }}">Kontak</a>
            </div>
            <div>
                <h4>Layanan</h4>
                <a href="{{ route('public.services') }}">Container Trucking</a>
                <a href="{{ route('public.services') }}">Freight Forwarding</a>
                <a href="{{ route('public.services') }}">Warehousing</a>
            </div>
            <div>
                <h4>Portal</h4>
                <a href="{{ route('public.track') }}">Lacak Kiriman</a>
                <a href="{{ route('login') }}">Masuk</a>
                <a href="{{ route('public.contact') }}">Request Quote</a>
            </div>
        </div>
        <div class="pub-footer__bottom">
            <span>&copy; {{ now()->year }} ZarTrack. Seluruh hak cipta dilindungi.</span>
            <span class="mono fs-12">Container Trucking · Logistics Platform</span>
        </div>
    </div>
</footer>