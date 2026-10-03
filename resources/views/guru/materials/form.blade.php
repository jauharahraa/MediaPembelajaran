@extends('layouts.app')
@section('title', $material->exists ? 'Edit Materi' : 'Tambah Materi')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<style>#editor { height: 320px; background: #fff; font-size: 1rem; }</style>
@endpush

@section('content')
<a href="{{ route('guru.rooms.show', $room) }}" class="text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>{{ $room->name }}</a>
<h1 class="h4 fw-bold my-3">{{ $material->exists ? 'Edit Materi' : 'Tambah Materi' }}</h1>
@include('partials.form-errors')

<div class="card-soft p-4">
    <form method="POST" enctype="multipart/form-data" id="material-form"
          action="{{ $material->exists ? route('guru.rooms.materials.update', [$room, $material]) : route('guru.rooms.materials.store', $room) }}">
        @csrf
        @if ($material->exists) @method('PUT') @endif

        <div class="mb-3">
            <label for="title" class="form-label fw-semibold">Judul materi</label>
            <input id="title" name="title" value="{{ old('title', $material->title) }}" required
                   class="form-control form-control-lg @error('title') is-invalid @enderror">
            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Isi materi</label>
            <div id="editor"></div>
            <input type="hidden" name="content" id="content">
            @error('content') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="mb-4">
            <label for="image" class="form-label fw-semibold">Gambar pendukung (opsional)</label>
            @if ($material->image_path)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $material->image_path) }}" class="img-fluid rounded-3" style="max-height: 160px;" alt="">
                    <div class="form-text">Pilih file baru untuk mengganti gambar ini.</div>
                </div>
            @endif
            <input id="image" type="file" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
            <div class="form-text">Format gambar, maksimal 2 MB.</div>
            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-accent px-4">Simpan Materi</button>
            <a href="{{ route('guru.rooms.show', $room) }}" class="btn btn-light rounded-pill px-4">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
const quill = new Quill('#editor', {
    theme: 'snow',
    placeholder: 'Tulis materi di sini...',
    modules: {
        toolbar: [
            [{ header: [2, 3, false] }],
            ['bold', 'italic', 'underline'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['blockquote', 'code-block', 'link'],
            ['clean'],
        ],
    },
});

const initial = @json(old('content', $material->content ?? ''));
if (initial) quill.clipboard.dangerouslyPasteHTML(initial);

document.getElementById('material-form').addEventListener('submit', function () {
    // Editor kosong dikirim sebagai string kosong supaya validasi server menolaknya
    document.getElementById('content').value = quill.getText().trim() === '' ? '' : quill.root.innerHTML;
});
</script>
@endpush