@extends('layouts.app')
@section('title', $room->exists ? 'Edit Room' : 'Buat Room')

@section('content')
<h1 class="h4 fw-bold mb-4">{{ $room->exists ? 'Edit Room' : 'Buat Room Baru' }}</h1>
@include('partials.form-errors')

<div class="card-soft p-4" style="max-width: 760px;">
    <form method="POST" action="{{ $room->exists ? route('guru.rooms.update', $room) : route('guru.rooms.store') }}">
        @csrf
        @if ($room->exists) @method('PUT') @endif

        <div class="row g-3">
            @foreach ([
                ['name', 'Nama room', 'Contoh: Materi IP Addressing'],
                ['subject', 'Mata pelajaran', ''],
                ['topic', 'Nama atau topik materi', 'Contoh: IP Addressing'],
                ['class_level', 'Kelas', 'Contoh: XI TKJ'],
            ] as [$field, $label, $placeholder])
                <div class="col-md-6">
                    <label for="{{ $field }}" class="form-label fw-semibold">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" placeholder="{{ $placeholder }}" required
                           value="{{ old($field, $room->$field ?: ($field === 'subject' ? 'Komputer dan Jaringan Dasar' : '')) }}"
                           class="form-control @error($field) is-invalid @enderror">
                    @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @endforeach

            <div class="col-12">
                <label for="description" class="form-label fw-semibold">Deskripsi materi</label>
                <textarea id="description" name="description" rows="3"
                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $room->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            @if ($room->exists)
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kode room</label>
                    <input class="form-control font-monospace" value="{{ $room->code }}" readonly>
                    <div class="form-text">Kode dibuat otomatis dan tidak bisa diubah.</div>
                </div>
            @else
                <div class="col-12"><div class="form-text">Kode room dibuat otomatis setelah room disimpan.</div></div>
            @endif

            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1"
                           @checked(old() ? old('is_active') : $room->is_active)>
                    <label class="form-check-label" for="is_active">Room aktif (siswa dapat bergabung dan mengerjakan kasus)</label>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-accent px-4">Simpan</button>
            <a href="{{ $room->exists ? route('guru.rooms.show', $room) : route('guru.rooms.index') }}" class="btn btn-light rounded-pill px-4">Batal</a>
        </div>
    </form>
</div>
@endsection