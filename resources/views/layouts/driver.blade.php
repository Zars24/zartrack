@extends('layouts.base')
@section('body')
<div class="driver-shell">
    <header class="driver-top">
        <div class="avatar avatar-sm">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
        <div class="flex-grow-1 min-w-0">
            <div class="fw-600 fs-13 text-truncate">@yield('title', 'Driver Portal')</div>
            <div class="fs-12 text-dim">{{ auth()->user()->name }}</div>
        </div>
        <button class="btn-icon theme-toggle" data-theme-toggle aria-label="Ganti tema">
            <span class="theme-sun"><x-icon name="sun" size="15" /></span>
            <span class="theme-moon"><x-icon name="moon" size="15" /></span>
        </button>
    </header>

    <main class="driver-body" id="main-content">
        @if (session('success'))
            <div class="alert alert-success alert-auto mb-3" role="status"><x-icon name="check" size="16" />{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger mb-3" role="alert"><x-icon name="alert" size="16" /><span>{{ $errors->first() }}</span></div>
        @endif
        @yield('content')
    </main>

    <nav class="driver-tabbar" aria-label="Navigasi driver">
        <a href="{{ route('driver.dashboard') }}" class="{{ request()->routeIs('driver.dashboard') ? 'active' : '' }}"><x-icon name="dashboard" size="18" />Tugas</a>
        <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}"><x-icon name="bell" size="18" />Notifikasi</a>
        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}"><x-icon name="user" size="18" />Profil</a>
        <form method="POST" action="{{ route('logout') }}" class="flex-fill d-flex">@csrf
            <button type="submit"><x-icon name="logout" size="18" />Keluar</button>
        </form>
    </nav>
</div>
@endsection