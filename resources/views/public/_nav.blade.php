<header class="pub-nav" id="pubNav">
    <div class="pub-nav__inner">
        <a href="{{ route('public.home') }}" class="brand">
            <span class="brand-mark">ZT</span><span class="brand-text">Zar<span>Track</span></span>
        </a>
        <nav class="pub-links" aria-label="Navigasi">
            <a href="{{ route('public.services') }}" class="{{ request()->routeIs('public.services') ? 'active' : '' }}">Layanan</a>
            <a href="{{ route('public.about') }}" class="{{ request()->routeIs('public.about') ? 'active' : '' }}">Tentang</a>
            <a href="{{ route('public.track') }}" class="{{ request()->routeIs('public.track*') ? 'active' : '' }}">Tracking</a>
            <a href="{{ route('public.contact') }}" class="{{ request()->routeIs('public.contact') ? 'active' : '' }}">Kontak</a>
        </nav>
        <div class="pub-actions">
            <button class="btn-icon theme-toggle" data-theme-toggle aria-label="Ganti tema">
                <span class="theme-sun"><x-icon name="sun" size="16" /></span>
                <span class="theme-moon"><x-icon name="moon" size="16" /></span>
            </button>
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-secondary">Masuk</a>
            <a href="{{ route('public.contact') }}" class="btn btn-sm btn-primary">Minta Penawaran</a>
            <button class="btn-icon pub-burger" data-pub-menu aria-expanded="false" aria-label="Buka menu"><x-icon name="menu" /></button>
        </div>
    </div>
    <div class="pub-mobile">
        <a href="{{ route('public.services') }}">Layanan</a>
        <a href="{{ route('public.about') }}">Tentang</a>
        <a href="{{ route('public.track') }}">Tracking</a>
        <a href="{{ route('public.contact') }}">Kontak</a>
        <a href="{{ route('login') }}">Masuk Portal</a>
    </div>
</header>