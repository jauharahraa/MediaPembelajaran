@extends('layouts.app')
@section('title', 'Dashboard Siswa')

@section('content')
<div class="welcome-card p-4 p-lg-5 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">Halo, {{ $user->name }}</h1>
        <p class="mb-0 opacity-75">{{ $user->kelas }} · Selamat belajar!</p>
    </div>
    <a href="{{ route('siswa.join') }}" class="btn btn-accent px-4"><i class="bi bi-key me-1"></i>Bergabung ke Room</a>
</div>

@php
    $cards = [
        ['Kasus Dikerjakan', 'answered', 'bi-pencil-square', 'primary'],
        ['Belum Dikerjakan', 'pending', 'bi-hourglass-split', 'warning'],
        ['Sudah Dinilai Guru', 'graded', 'bi-patch-check', 'success'],
    ];
@endphp

<div class="row g-3 mb-4">
    @foreach ($cards as [$label, $key, $icon, $color])
        <div class="col-12 col-md-4">
            <div class="card-soft p-3 h-100 d-flex align-items-center gap-3">
                <div class="stat-icon bg-{{ $color }}-subtle text-{{ $color }}-emphasis"><i class="bi {{ $icon }}"></i></div>
                <div>
                    <div class="fs-3 fw-bold lh-1">{{ $stats[$key] }}</div>
                    <div class="text-muted small">{{ $label }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<h2 class="h5 fw-bold mb-3">Room yang Diikuti</h2>
@if ($rooms->isEmpty())
    <div class="card-soft p-4 text-center mb-4">
        <p class="text-muted mb-3">Anda belum bergabung ke room mana pun. Minta kode room kepada guru Anda.</p>
        <a href="{{ route('siswa.join') }}" class="btn btn-accent px-4">Masukkan Kode Room</a>
    </div>
@else
    <div class="row g-3 mb-4">
        @foreach ($rooms as $room)
            <div class="col-md-6 col-xl-4">
                <div class="card-soft p-3 h-100 d-flex flex-column">
                    <div class="room-cover mb-3"><i class="bi bi-hdd-network"></i></div>
                    <h3 class="h6 fw-bold mb-1">
                        {{ $room->name }}
                        @unless ($room->is_active) <span class="badge text-bg-secondary ms-1">Nonaktif</span> @endunless
                    </h3>
                    <p class="text-muted small mb-2">{{ $room->topic }}</p>
                    <div class="small text-muted mb-3">
                        <i class="bi bi-person-badge me-1"></i>{{ $room->teacher->name }} · {{ $room->cases_count }} kasus
                    </div>
                    <a href="{{ route('siswa.rooms.show', $room) }}" class="btn btn-accent mt-auto">Buka Room</a>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="card-soft p-3 p-lg-4">
    <h2 class="h5 fw-bold mb-3">Aktivitas Pengerjaan Terbaru</h2>
    @if ($recent->isEmpty())
        <p class="text-muted mb-0">Belum ada aktivitas pengerjaan.</p>
    @else
        <ul class="list-group list-group-flush">
            @foreach ($recent as $sub)
                <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="fw-semibold">{{ $sub->case->title }}</div>
                        <small class="text-muted">{{ $sub->case->room->name }} · {{ $sub->submitted_at->translatedFormat('d M Y, H:i') }}</small>
                    </div>
                    <span class="badge text-bg-{{ $sub->attempt_number == 1 ? 'secondary' : 'primary' }}">
                        {{ $sub->attempt_number == 1 ? 'Jawaban pertama' : 'Revisi' }}
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection