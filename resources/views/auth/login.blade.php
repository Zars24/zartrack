@extends('layouts.base')
@section('title', 'Masuk')
@section('body')
<div class="auth-wrap">
    <aside class="auth-visual">
        <a href="{{ route('public.home') }}" class="brand d-flex align-items-center gap-2 text-white">
            <span class="brand-mark">ZT</span>
            <span class="brand-text" style="color:#fff">Zar<span style="color:#93b8ff">Track</span></span>
        </a>
        <div>
            <span class="eyebrow"><span class="pulse"></span>Operations Platform</span>
            <h2>Satu login untuk seluruh operasional container.</h2>
            <p>Order, dispatch, tracking GPS, dokumen, hingga invoice — semuanya dalam satu alur teraudit.</p>
        </div>
        <div class="auth-footnote">&copy; {{ now()->year }} ZarTrack · Container Trucking &amp; Logistics</div>
    </aside>

    <div class="auth-form-side">
        <div class="auth-card reveal">
            <div class="auth-seal d-lg-none">
                <span class="brand-mark">ZT</span>
                <span class="brand-text">Zar<span>Track</span></span>
            </div>
            <h1>Masuk ke ZarTrack</h1>
            <p class="sub">Gunakan akun yang diberikan admin.</p>

            @if ($errors->any())
                <div class="alert alert-danger mb-3" role="alert"><x-icon name="alert" size="16" />{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@perusahaan.id">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input id="password" type="password" name="password" class="form-control" required autocomplete="current-password" placeholder="••••••••">
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label fs-13" for="remember">Ingat saya</label>
                    </div>
                    <a href="{{ route('public.home') }}" class="fs-13">Beranda</a>
                </div>
                <button class="btn btn-primary w-100 btn-lg">Masuk</button>
            </form>

            <div class="demo-accounts">
                <strong>Akun demo</strong>
                <div class="mt-1 d-grid gap-1">
                    <div>Super Admin — <code data-fill="super@zartrack.id">super@zartrack.id</code></div>
                    <div>Ops Admin — <code data-fill="ops@zartrack.id">ops@zartrack.id</code></div>
                    <div>Driver — <code data-fill="driver@zartrack.id">driver@zartrack.id</code></div>
                    <div>Customer — <code data-fill="customer@zartrack.id">customer@zartrack.id</code></div>
                    <div class="mt-1">Password: <code data-fill-pass>password</code></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-fill]').forEach((el) => {
        el.addEventListener('click', () => {
            document.getElementById('email').value = el.dataset.fill;
            document.getElementById('password').value = 'password';
        });
    });
});
</script>
@endpush