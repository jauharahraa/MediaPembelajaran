@extends('layouts.app')
@section('title', $video->exists ? 'Edit Video' : 'Tambah Video')

@section('content')
<a href="{{ route('guru.rooms.show', $room) }}" class="text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>{{ $room->name }}</a>
<h1 class="h4 fw-bold my-3">{{ $video->exists ? 'Edit Video' : 'Tambah Video' }}</h1>
@include('partials.form-errors')

<div class="card-soft p-4" style="max-width: 760px;">
    <form method="POST"
          action="{{ $video->exists ? route('guru.rooms.videos.update', [$room, $video]) : route('guru.rooms.videos.store', $room) }}">
        @csrf
        @if ($video->exists) @method('PUT') @endif

        <div class="mb-3">
            <label for="title" class="form-label fw-semibold">Judul video</label>
            <input id="title" name="title" value="{{ old('title', $video->title) }}" required
                   class="form-control @error('title') is-invalid @enderror">
            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="youtube_url" class="form-label fw-semibold">Tautan YouTube</label>
            <input id="youtube_url" name="youtube_url" value="{{ old('youtube_url', $video->youtube_url) }}" required
                   placeholder="https://www.youtube.com/watch?v=..."
                   class="form-control @error('youtube_url') is-invalid @enderror">
            @error('youtube_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
            <div class="ratio ratio-16x9 rounded-4 overflow-hidden bg-light d-none" id="preview-box">
                <iframe id="preview" allowfullscreen title="Preview video"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allow="accelerometer; encrypted-media; picture-in-picture"></iframe>
            </div>
            <div class="text-muted small" id="preview-hint">Preview muncul setelah tautan YouTube valid.</div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-accent px-4">Simpan Video</button>
            <a href="{{ route('guru.rooms.show', $room) }}" class="btn btn-light rounded-pill px-4">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const input = document.getElementById('youtube_url');
const box = document.getElementById('preview-box');
const frame = document.getElementById('preview');
const hint = document.getElementById('preview-hint');

function extractId(url) {
    const m = url.match(/(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/);
    return m ? m[1] : null;
}

function updatePreview() {
    const id = extractId(input.value.trim());
    if (id) {
        frame.src = 'https://www.youtube.com/embed/' + id;
        box.classList.remove('d-none');
        hint.classList.add('d-none');
    } else {
        frame.removeAttribute('src');
        box.classList.add('d-none');
        hint.classList.remove('d-none');
    }
}

input.addEventListener('input', updatePreview);
updatePreview();
</script>
@endpush