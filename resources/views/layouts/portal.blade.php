@extends('layouts.base')
@php
    $items = [
        ['customer.dashboard', 'Dashboard', 'dashboard'],
        ['customer.orders', 'Order', 'order'],
        ['customer.invoices', 'Invoice', 'invoice'],
        ['customer.documents', 'Dokumen', 'doc'],
    ];
@endphp
@section('body')
@php $pageTitle = trim($__env->yieldContent('title')) ?: 'Portal'; @endphp
<a href="#main-content" class="skip-link">Lewati ke konten</a>
<div class="app-shell">
    <div class="sidebar-backdrop"></div>
    <aside class="sidebar" id="app-sidebar" aria-label="Navigasi portal">
        <a href="{{ route('customer.dashboard') }}" class="sidebar__brand">
            <span class="brand-mark">ZT</span>
            <span class="brand-text">Zar<span>Track</span><small class="brand-sub">Customer Portal</small></span>
        </a>
        <nav class="sidebar__nav">
            <div class="nav-group">
                <div class="nav-label">Portal Customer</div>
                @foreach ($items as [$route, $label, $icon])
                    <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs(str_replace('.index', '.*', $route)) ? 'active' : '' }}">
                        <span class="nav-link__icon"><x-icon :name="$icon" /></span>
                        <span>{{ $label }}</span>
                    </a>
                @endforeach
            </div>
            <div class="nav-group">
                <div class="nav-label">Publik</div>
                <a href="{{ route('public.track') }}" class="nav-link"><span class="nav-link__icon"><x-icon name="map" /></span> <span>Lacak Kiriman</span></a>
            </div>
        </nav>
        <div class="sidebar__foot">
            <div class="sidebar-user">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                <div class="sidebar-user__meta">
                    <div class="sidebar-user__name">{{ auth()->user()->name }}</div>
                    <div class="sidebar-user__role">{{ auth()->user()->customer->name ?? 'Customer' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn-icon" aria-label="Keluar"><x-icon name="logout" /></button></form>
            </div>
        </div>
    </aside>

    <div class="main-col">
        <header class="topbar">
            <button class="btn-icon d-lg-none" data-sidebar-toggle aria-controls="app-sidebar" aria-expanded="false" aria-label="Buka menu"><x-icon name="menu" /></button>
            <div class="topbar__lead">
                <nav class="crumb" aria-label="Breadcrumb">
                    <a href="{{ route('customer.dashboard') }}">Portal</a>
                    <span class="crumb__sep">/</span>
                    <span class="crumb__current">{{ $pageTitle }}</span>
                </nav>
            </div>
            <span class="topbar__spacer"></span>
            <span class="topbar__date"><span class="topbar__dot"></span>{{ now()->translatedFormat('d M Y') }}</span>
            <a href="{{ route('notifications.index') }}" class="btn-icon position-relative" aria-label="Notifikasi">
                <x-icon name="bell" />
                @if (($notifCount ?? 0) > 0)
                    <span class="notif-badge">{{ $notifCount > 99 ? '99+' : $notifCount }}</span>
                @endif
            </a>
            <button class="btn-icon theme-toggle" data-theme-toggle aria-label="Ganti tema">
                <span class="theme-sun"><x-icon name="sun" /></span>
                <span class="theme-moon"><x-icon name="moon" /></span>
            </button>
            <div class="dropdown">
                <button class="user-chip" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu akun">
                    <span class="avatar avatar-sm">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    <span class="user-chip__meta">
                        <span class="user-chip__name d-block">{{ auth()->user()->name }}</span>
                        <span class="user-chip__role">Customer</span>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header">{{ auth()->user()->email }}</li>
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><x-icon name="user" size="16" />Profil saya</a></li>
                    <li><a class="dropdown-item" href="{{ route('notifications.index') }}"><x-icon name="bell" size="16" />Notifikasi</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item" type="submit"><x-icon name="logout" size="16" />Keluar</button></form></li>
                </ul>
            </div>
        </header>
        <main class="content" id="main-content">
            @if (session('success'))<div class="alert alert-success alert-auto mb-3" role="status"><x-icon name="check" size="16" />{{ session('success') }}</div>@endif
            @if ($errors->any())<div class="alert alert-danger mb-3" role="alert"><x-icon name="alert" size="16" /><span>{{ $errors->first() }}</span></div>@endif
            @yield('content')
        </main>
    </div>
</div>
@endsection