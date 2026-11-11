@extends('layouts.admin')
@section('title', 'Activity Log')
@section('content')
<div class="page-head">
    <div class="page-head__text">
        <span class="page-eyebrow">Sistem</span>
        <h1>Activity Log</h1>
        <p>Jejak tindakan penting di sistem.</p>
    </div>
</div>
<div class="card">
    <div class="table-wrap">
        @if ($logs->isEmpty())
            <div class="empty-state"><x-icon name="log" /><h3>Belum ada aktivitas</h3></div>
        @else
        <table class="table">
            <thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Deskripsi</th><th>IP</th></tr></thead>
            <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td class="fs-12 mono text-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                    <td class="fs-13">{{ $log->user->name ?? 'Sistem' }}</td>
                    <td><span class="badge-status st-confirmed mono">{{ $log->action }}</span></td>
                    <td class="fs-13 text-muted-2">{{ $log->description }}</td>
                    <td class="fs-12 mono text-dim">{{ $log->ip }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @if ($logs->hasPages())
        <div class="card__foot">{{ $logs->links() }}</div>
    @endif
</div>
@endsection