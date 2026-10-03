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

<form method="POST" enctype="multipart/form-data" id="material-form"
      action="{{ $material->exists ? route('guru.rooms.materials.update', [$room, $material]) : route('guru.rooms.materials.store', $room) }}">
    @csrf
    @if ($material->exists) @method('PUT') @endif

    <div class="card-soft p-4 mb-3">
        <label for="title" class="form-label fw-semibold">Judul materi</label>
        <input id="title" name="title" value="{{ old('title', $material->title) }}" required
               class="form-control form-control-lg @error('title') is-invalid @enderror">
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="alert alert-info border-0 rounded-4 small">
        <i class="bi bi-info-circle me-1"></i>
        Materi bisa <strong>diketik</strong>, <strong>diunggah sebagai file</strong> (PDF, Word, PowerPoint), atau keduanya. Isi minimal salah satu.
    </div>

    <div class="card-soft p-4 mb-3">
        <h2 class="h5 fw-bold mb-3"><i class="bi bi-keyboard me-2"></i>Ketik materi</h2>
        <div id="editor"></div>
        <input type="hidden" name="content" id="content">
        @error('content') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
    </div>

    <div class="card-soft p-4 mb-3">
        <h2 class="h5 fw-bold mb-3"><i class="bi bi-file-earmark-arrow-up me-2"></i>Unggah file</h2>

        @if ($material->hasFile())
            <div class="border rounded-4 p-3 bg-light d-flex align-items-center gap-3 mb-3">
                <i class="bi {{ $material->fileIcon() }} fs-2"></i>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="fw-semibold text-break">{{ $material->file_name }}</div>
                    <div class="small text-muted">{{ $material->fileTypeLabel() }} · {{ $material->fileSizeLabel() }}</div>
                </div>
                <a href="{{ route('materials.file', ['material' => $material, 'download' => 1]) }}" class="btn btn-sm btn-outline-primary rounded-pill">Unduh</a>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remove_file" id="remove_file" value="1" @checked(old('remove_file'))>
                <label class="form-check-label" for="remove_file">Hapus file ini</label>
            </div>
            <div class="form-text mb-2">Pilih file baru di bawah untuk menggantikan file saat ini.</div>
        @endif

        <input id="file" type="file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx"
               class="form-control @error('file') is-invalid @enderror">
        <div class="form-text">PDF, Word (.doc, .docx), atau PowerPoint (.ppt, .pptx). Maksimal 20 MB.</div>
        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="card-soft p-4 mb-3">
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

    <div class="d-flex gap-2 mb-4">
        <button class="btn btn-accent px-4">Simpan Materi</button>
        <a href="{{ route('guru.rooms.show', $room) }}" class="btn btn-light rounded-pill px-4">Batal</a>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
const quill = new Quill('#editor', {
    theme: 'snow',
    placeholder: 'Tulis materi di sini (boleh dikosongkan jika mengunggah file)...',
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
    document.getElementById('content').value = quill.getText().trim() === '' ? '' : quill.root.innerHTML;
});

document.getElementById('file').addEventListener('change', function () {
    const f = this.files[0];
    if (f && f.size > 20 * 1024 * 1024) {
        alert('Ukuran file maksimal 20 MB.');
        this.value = '';
    }
});
</script>
@endpush