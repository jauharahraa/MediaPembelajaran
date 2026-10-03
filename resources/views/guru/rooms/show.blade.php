@extends('layouts.app')
@section('title', $room->name)

@section('content')
<a href="{{ route('guru.rooms.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>Semua room</a>

<div class="card-soft p-4 my-3">
    <div class="d-flex flex-wrap justify-content-between gap-3">
        <div>
            <span class="badge text-bg-{{ $room->is_active ? 'success' : 'secondary' }} mb-2">{{ $room->is_active ? 'Aktif' : 'Nonaktif' }}</span>
            <h1 class="h4 fw-bold mb-1">{{ $room->name }}</h1>
            <p class="text-muted mb-2">{{ $room->subject }} · {{ $room->topic }} · {{ $room->class_level }}</p>
            @if ($room->description) <p class="mb-0">{{ $room->description }}</p> @endif
        </div>
        <div class="text-lg-end">
            <div class="small text-muted">Kode room (bagikan ke siswa)</div>
            <div class="d-flex align-items-center gap-2 justify-content-lg-end">
                <span class="fs-3 fw-bold font-monospace text-primary" id="room-code">{{ $room->code }}</span>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" id="copy-code"><i class="bi bi-clipboard me-1"></i>Salin</button>
            </div>
            <div class="small text-muted mt-1"><i class="bi bi-people me-1"></i>{{ $room->members_count }} siswa bergabung</div>
        </div>
    </div>
    <hr>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('guru.rooms.edit', $room) }}" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-pencil me-1"></i>Edit Room</a>
        <form method="POST" action="{{ route('guru.rooms.toggle', $room) }}">
            @csrf @method('PATCH')
            <button class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-toggle-{{ $room->is_active ? 'on' : 'off' }} me-1"></i>{{ $room->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
        </form>
        @include('partials.delete-button', [
            'action'  => route('guru.rooms.destroy', $room),
            'message' => 'Hapus room ini? Semua materi, video, kasus, jawaban, dan nilai siswa di dalamnya ikut terhapus dan tidak bisa dikembalikan.',
            'label'   => 'Hapus Room',
        ])
    </div>
</div>

{{-- Materi --}}
<div class="card-soft p-4 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 fw-bold mb-0"><i class="bi bi-journal-text me-2"></i>Materi Pembelajaran</h2>
        <a href="{{ route('guru.rooms.materials.create', $room) }}" class="btn btn-sm btn-accent"><i class="bi bi-plus-lg me-1"></i>Tambah Materi</a>
    </div>
    @forelse ($room->materials as $material)
        <div class="d-flex justify-content-between align-items-center py-2 border-top gap-2">
            <div class="fw-semibold">{{ $material->title }}
                @if ($material->image_path)<i class="bi bi-image text-muted ms-1" title="Ada gambar"></i>@endif
            </div>
            <div class="d-flex gap-1">
                <a href="{{ route('guru.rooms.materials.edit', [$room, $material]) }}" class="btn btn-sm btn-outline-primary rounded-pill" title="Edit"><i class="bi bi-pencil"></i></a>
                @include('partials.delete-button', ['action' => route('guru.rooms.materials.destroy', [$room, $material]), 'message' => 'Hapus materi ini?'])
            </div>
        </div>
    @empty
        <p class="text-muted mb-0">Belum ada materi.</p>
    @endforelse
</div>

{{-- Video --}}
<div class="card-soft p-4 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 fw-bold mb-0"><i class="bi bi-play-circle me-2"></i>Video Pembelajaran</h2>
        <a href="{{ route('guru.rooms.videos.create', $room) }}" class="btn btn-sm btn-accent"><i class="bi bi-plus-lg me-1"></i>Tambah Video</a>
    </div>
    @forelse ($room->videos as $video)
        <div class="d-flex justify-content-between align-items-center py-2 border-top gap-2">
            <div class="d-flex align-items-center gap-3">
                <img src="https://img.youtube.com/vi/{{ $video->youtube_id }}/mqdefault.jpg" width="96" class="rounded-3" alt="">
                <div class="fw-semibold">{{ $video->title }}</div>
            </div>
            <div class="d-flex gap-1">
                <a href="{{ route('guru.rooms.videos.edit', [$room, $video]) }}" class="btn btn-sm btn-outline-primary rounded-pill" title="Edit"><i class="bi bi-pencil"></i></a>
                @include('partials.delete-button', ['action' => route('guru.rooms.videos.destroy', [$room, $video]), 'message' => 'Hapus video ini?'])
            </div>
        </div>
    @empty
        <p class="text-muted mb-0">Belum ada video.</p>
    @endforelse
</div>

{{-- Kasus --}}
<div class="card-soft p-4 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 fw-bold mb-0"><i class="bi bi-puzzle me-2"></i>Kasus Case Method</h2>
        <a href="{{ route('guru.rooms.cases.create', $room) }}" class="btn btn-sm btn-accent"><i class="bi bi-plus-lg me-1"></i>Tambah Kasus</a>
    </div>
    @if ($room->cases->isEmpty())
        <p class="text-muted mb-0">Belum ada kasus. Jumlah kasus tidak dibatasi.</p>
    @else
        <ul class="list-unstyled mb-0" id="case-list">
            @foreach ($room->cases as $case)
                <li class="d-flex align-items-center gap-2 py-2 border-top" data-id="{{ $case->id }}">
                    <span class="badge text-bg-primary case-number">{{ $loop->iteration }}</span>
                    <a href="{{ route('guru.rooms.cases.show', [$room, $case]) }}" class="fw-semibold text-decoration-none flex-grow-1">{{ $case->title }}</a>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" data-move="up" title="Naik"><i class="bi bi-arrow-up"></i></button>
                        <button type="button" class="btn btn-outline-secondary" data-move="down" title="Turun"><i class="bi bi-arrow-down"></i></button>
                    </div>
                    <a href="{{ route('guru.rooms.cases.edit', [$room, $case]) }}" class="btn btn-sm btn-outline-primary rounded-pill" title="Edit"><i class="bi bi-pencil"></i></a>
                    @include('partials.delete-button', [
                        'action'  => route('guru.rooms.cases.destroy', [$room, $case]),
                        'message' => 'Hapus kasus ini? Jawaban dan nilai siswa pada kasus ini ikut terhapus.',
                    ])
                </li>
            @endforeach
        </ul>
        <div class="small text-muted mt-2" id="reorder-status">Gunakan tombol panah untuk mengatur urutan kasus.</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.getElementById('copy-code').addEventListener('click', function () {
    navigator.clipboard.writeText(document.getElementById('room-code').textContent.trim());
    this.innerHTML = '<i class="bi bi-check2 me-1"></i>Tersalin';
});

const list = document.getElementById('case-list');
if (list) {
    const status = document.getElementById('reorder-status');

    async function saveOrder() {
        const order = [...list.children].map(li => Number(li.dataset.id));
        try {
            const res = await fetch(@json(route('guru.rooms.cases.reorder', $room)), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                },
                body: JSON.stringify({ order }),
            });
            status.textContent = res.ok ? 'Urutan tersimpan.' : 'Gagal menyimpan urutan. Muat ulang halaman.';
        } catch (e) {
            status.textContent = 'Gagal menyimpan urutan. Periksa koneksi.';
        }
    }

    list.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-move]');
        if (!btn) return;
        const li = btn.closest('li');

        if (btn.dataset.move === 'up' && li.previousElementSibling) {
            li.parentNode.insertBefore(li, li.previousElementSibling);
        } else if (btn.dataset.move === 'down' && li.nextElementSibling) {
            li.parentNode.insertBefore(li.nextElementSibling, li);
        } else {
            return;
        }

        list.querySelectorAll('.case-number').forEach((el, i) => el.textContent = i + 1);
        saveOrder();
    });
}
</script>
@endpush