@extends('layouts.app')
@section('title', 'Room Saya')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1 class="h4 fw-bold mb-0">Room Saya</h1>
    <a href="{{ route('siswa.join') }}" class="btn btn-accent"><i class="bi bi-key me-1"></i>Bergabung ke Room</a>
</div>

@if ($rooms->isEmpty())
    <div class="card-soft p-5 text-center">
        <p class="text-muted mb-3">Anda belum bergabung ke room mana pun. Minta kode room kepada guru Anda.</p>
        <a href="{{ route('siswa.join') }}" class="btn btn-accent px-4">Masukkan Kode Room</a>
    </div>
@else
    <div class="row g-3">
        @foreach ($rooms as $room)
            <div class="col-md-6 col-xl-4">
                <div class="card-soft p-3 h-100 d-flex flex-column">
                    <div class="room-cover mb-3"><i class="bi bi-hdd-network"></i></div>
                    <h2 class="h6 fw-bold mb-1">
                        {{ $room->name }}
                        @unless ($room->is_active) <span class="badge text-bg-secondary ms-1">Nonaktif</span> @endunless
                    </h2>
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
@endsection