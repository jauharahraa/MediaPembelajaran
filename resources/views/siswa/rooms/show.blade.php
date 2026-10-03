@extends('layouts.app')
@section('title', $room->name)

@section('content')
<a href="{{ route('siswa.rooms.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>Room Saya</a>

<div class="card-soft p-4 my-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
        <div>
            <h1 class="h4 fw-bold mb-1">{{ $room->name }}</h1>
            <p class="text-muted mb-0">{{ $room->topic }} · {{ $room->class_level }} · Guru: {{ $room->teacher->name }}</p>
        </div>
        @if ($locked) <span class="badge text-bg-secondary">Nonaktif</span> @endif
    </div>
</div>

@if ($locked)
    <div class="alert alert-warning border-0 rounded-4">
        <i class="bi bi-lock me-2"></i>Room ini sedang dinonaktifkan oleh guru. Materi, video, dan kasus tidak bisa dibuka sementara.
    </div>
@endif

<ul class="nav nav-pills gap-2 mb-3 flex-nowrap overflow-auto pb-1" role="tablist">
    @foreach ($tabs as $id => [$label, $icon])
        <li class="nav-item" role="presentation">
            <button type="button" role="tab" data-bs-toggle="tab" data-bs-target="#{{ $id }}"
                    class="nav-link text-nowrap {{ $loop->first ? 'active' : '' }}">
                <i class="bi {{ $icon }} me-1"></i>{{ $label }}
            </button>
        </li>
    @endforeach
</ul>

<div class="tab-content">

    {{-- Informasi --}}
    <div class="tab-pane fade show active" id="info" role="tabpanel">
        <div class="card-soft p-4 mb-3">
            <dl class="row mb-0">
                <dt class="col-sm-4">Mata pelajaran</dt><dd class="col-sm-8">{{ $room->subject }}</dd>
                <dt class="col-sm-4">Topik materi</dt><dd class="col-sm-8">{{ $room->topic }}</dd>
                <dt class="col-sm-4">Kelas</dt><dd class="col-sm-8">{{ $room->class_level }}</dd>
                <dt class="col-sm-4">Guru</dt><dd class="col-sm-8">{{ $room->teacher->name }}</dd>
                <dt class="col-sm-4">Isi room</dt>
                <dd class="col-sm-8 mb-0">{{ $room->materials->count() }} materi · {{ $room->videos->count() }} video · {{ $room->cases->count() }} kasus</dd>
            </dl>
            @if ($room->description)
                <hr>
                <p class="mb-0" style="white-space: pre-line;">{{ $room->description }}</p>
            @endif
        </div>
        <div class="card-soft p-4">
            <h2 class="h6 fw-bold mb-3">Alur belajar</h2>
            <div class="d-flex flex-wrap gap-3">
                @foreach (['Baca materi', 'Tonton video', 'Kerjakan kasus', 'Revisi satu kali', 'Lihat nilai'] as $i => $step)
                    <div class="d-flex align-items-center gap-2">
                        <span class="step-number">{{ $i + 1 }}</span><span class="fw-semibold">{{ $step }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @unless ($locked)
        {{-- Materi --}}
        <div class="tab-pane fade" id="materi" role="tabpanel">
            @forelse ($room->materials as $material)
                <div class="card-soft p-4 mb-3">
                    <h2 class="h5 fw-bold">{{ $material->title }}</h2>

                    @if ($material->image_path)
                        <img src="{{ asset('storage/' . $material->image_path) }}" class="img-fluid rounded-3 mb-3" alt="Gambar materi {{ $material->title }}">
                    @endif

                    @if ($material->content)
                        {{-- Isi sudah dibersihkan oleh Purify saat guru menyimpan materi --}}
                        <div class="material-content mb-3">{!! $material->content !!}</div>
                    @endif

                    @if ($material->hasFile())
                        <div class="border rounded-4 p-3 bg-light d-flex flex-wrap align-items-center gap-3">
                            <i class="bi {{ $material->fileIcon() }} fs-1"></i>
                            <div class="flex-grow-1" style="min-width: 0;">
                                <div class="fw-semibold text-break">{{ $material->file_name }}</div>
                                <div class="small text-muted">{{ $material->fileTypeLabel() }} · {{ $material->fileSizeLabel() }}</div>
                            </div>
                            <div class="d-flex gap-2">
                                @if ($material->isPdf())
                                    <a href="{{ route('materials.file', $material) }}" target="_blank" rel="noopener"
                                    class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-box-arrow-up-right me-1"></i>Buka</a>
                                @endif
                                <a href="{{ route('materials.file', ['material' => $material, 'download' => 1]) }}"
                                class="btn btn-sm btn-accent"><i class="bi bi-download me-1"></i>Unduh</a>
                            </div>
                        </div>

                        @if ($material->isPdf())
                            {{-- Pratinjau disembunyikan di ponsel karena banyak browser ponsel tidak menampilkan PDF di dalam halaman --}}
                            <iframe src="{{ route('materials.file', $material) }}" loading="lazy"
                                    title="Pratinjau {{ $material->title }}"
                                    class="w-100 rounded-4 border mt-3 d-none d-md-block" style="height: 70vh;"></iframe>
                        @endif
                    @endif
                </div>
            @empty
                <div class="card-soft p-4 text-muted">Guru belum menambahkan materi.</div>
            @endforelse
        </div>

        {{-- Video --}}
        <div class="tab-pane fade" id="video" role="tabpanel">
            @forelse ($room->videos as $video)
                <div class="card-soft p-4 mb-3">
                    <h2 class="h5 fw-bold mb-3">{{ $video->title }}</h2>
                    <div class="ratio ratio-16x9 rounded-4 overflow-hidden">
                        <iframe src="https://www.youtube.com/embed/{{ $video->youtube_id }}" title="{{ $video->title }}"
                                loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"
                                allow="accelerometer; encrypted-media; picture-in-picture"></iframe>
                    </div>
                </div>
            @empty
                <div class="card-soft p-4 text-muted">Guru belum menambahkan video.</div>
            @endforelse
        </div>

        {{-- Kuis --}}
        <div class="tab-pane fade" id="kuis" role="tabpanel">
            @forelse ($caseRows as $row)
                <a href="{{ route('siswa.rooms.cases.show', [$room, $row['case']]) }}"
                   class="card-soft p-3 mb-3 d-block text-decoration-none text-body">
                    <div class="d-flex gap-3">
                        <span class="step-number flex-shrink-0">{{ $loop->iteration }}</span>
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ $row['case']->title }}</div>
                            <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($row['case']->narrative, 140) }}</p>
                            <div class="d-flex flex-wrap gap-2">
                                @include('partials.status-badge', ['status' => $row['status']])
                                <span class="badge {{ $row['fbClass'] }}">Feedback AI: {{ $row['fbLabel'] }}</span>
                                <span class="badge {{ $row['graded'] ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    Penilaian guru: {{ $row['graded'] ? 'Sudah' : 'Belum' }}
                                </span>
                            </div>
                        </div>
                        <i class="bi bi-chevron-right align-self-center text-muted"></i>
                    </div>
                </a>
            @empty
                <div class="card-soft p-4 text-muted">Guru belum membuat kasus.</div>
            @endforelse
        </div>
    @endunless

    {{-- Hasil --}}
    <div class="tab-pane fade" id="hasil" role="tabpanel">
        <div class="card-soft p-4">
            <p class="text-muted">Lihat jawaban terakhir, feedback AI terbaru, dan nilai dari guru untuk setiap kasus.</p>
            <a href="{{ route('siswa.rooms.results.index', $room) }}" class="btn btn-accent px-4">Lihat Hasil Pengerjaan</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Membuka tab sesuai alamat, misalnya /siswa/rooms/1#kuis
    if (/^#[a-z]+$/.test(location.hash)) {
        const trigger = document.querySelector('[data-bs-target="' + location.hash + '"]');
        if (trigger) window.bootstrap.Tab.getOrCreateInstance(trigger).show();
    }

    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function () {
            history.replaceState(null, '', btn.dataset.bsTarget);
        });
    });
});
</script>
@endpush