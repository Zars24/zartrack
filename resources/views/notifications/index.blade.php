@extends('layouts.base')
@section('title', 'Notifikasi')
@section('body')
<div class="page-narrow">
    <a href="{{ auth()->user()->homeRoute() }}" class="back-link"><x-icon name="arrow" size="13" />Kembali</a>
    <div class="page-head mb-3">
        <div class="page-head__text">
            <span class="page-eyebrow">Pusat Aktivitas</span>
            <h1>Notifikasi</h1>
        </div>
        <div class="page-head__actions">
            <form method="POST" action="{{ route('notifications.readAll') }}">@csrf<button class="btn btn-sm btn-outline-secondary"><x-icon name="check" size="14" />Tandai semua dibaca</button></form>
        </div>
    </div>
    <div class="card">
        @forelse ($notifications as $n)
            <form method="POST" action="{{ route('notifications.read', $n->id) }}" class="border-bottom">
                @csrf
                <button class="w-100 text-start border-0 bg-transparent p-3 d-flex gap-3" style="color:inherit">
                    <span class="stat-icon" style="width:34px;height:34px;border-radius:10px"><x-icon name="bell" size="16" /></span>
                    <span class="flex-grow-1 min-w-0">
                        <span class="d-block fw-600 fs-13 {{ $n->read_at ? 'text-dim' : '' }}">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                        <span class="d-block fs-13 text-muted-2">{{ $n->data['message'] ?? '' }}</span>
                        <span class="d-block fs-12 text-dim mt-1">{{ $n->created_at->diffForHumans() }}</span>
                    </span>
                    @unless ($n->read_at)<span class="badge-status st-confirmed align-self-center">baru</span>@endunless
                </button>
            </form>
        @empty
            <div class="empty-state"><x-icon name="bell" /><h3>Tidak ada notifikasi</h3><p class="mb-0">Semua sudah bersih.</p></div>
        @endforelse
    </div>
    @if ($notifications->hasPages())
        <div class="mt-3">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection