@extends('layouts.app')
@section('title', 'Room Materi')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1 class="h4 fw-bold mb-0">Room Materi</h1>
    <a href="{{ route('guru.rooms.create') }}" class="btn btn-accent"><i class="bi bi-plus-lg me-1"></i>Buat Room</a>
</div>

@if ($rooms->isEmpty())
    <div class="card-soft p-5 text-center">
        <div class="stat-icon bg-primary-subtle text-primary mb-3 mx-auto"><i class="bi bi-door-open"></i></div>
        <h2 class="h5 fw-bold">Belum ada room</h2>
        <p class="text-muted">Satu room mewakili satu materi, misalnya IP Addressing atau Routing.</p>
        <a href="{{ route('guru.rooms.create') }}" class="btn btn-accent px-4">Buat Room Pertama</a>
    </div>
@else
    <div class="row g-3">
        @foreach ($rooms as $room)
            <div class="col-md-6 col-xl-4">
                <div class="card-soft p-3 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge text-bg-{{ $room->is_active ? 'success' : 'secondary' }}">{{ $room->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        <span class="font-monospace fw-bold text-primary">{{ $room->code }}</span>
                    </div>
                    <h2 class="h5 fw-bold mb-1">{{ $room->name }}</h2>
                    <p class="text-muted small mb-3">{{ $room->topic }} · {{ $room->class_level }}</p>

                    <div class="row g-2 text-center mb-3">
                        @foreach ([['members_count', 'Siswa'], ['materials_count', 'Materi'], ['videos_count', 'Video'], ['cases_count', 'Kasus']] as [$field, $label])
                            <div class="col-3">
                                <div class="bg-light rounded-3 py-2">
                                    <div class="fw-bold">{{ $room->$field }}</div>
                                    <div class="small text-muted">{{ $label }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex gap-2 mt-auto">
                        <a href="{{ route('guru.rooms.show', $room) }}" class="btn btn-accent flex-grow-1">Kelola</a>
                        <a href="{{ route('guru.rooms.edit', $room) }}" class="btn btn-outline-primary rounded-pill" title="Edit"><i class="bi bi-pencil"></i></a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection