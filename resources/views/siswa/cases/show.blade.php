@extends('layouts.app')
@section('title', $case->title)

@section('content')
@php
    $aspects = ['identifikasi' => 'Identifikasi Masalah', 'analisis' => 'Analisis Masalah', 'solusi' => 'Solusi'];
    $latest = $submissions->last();
    $status = $case->statusFor(auth()->id());
    $hasForm = in_array($mode, ['pertama', 'revisi']);
@endphp

<a href="{{ route('siswa.rooms.show', $room) }}#kuis" class="text-decoration-none small">
    <i class="bi bi-arrow-left me-1"></i>{{ $room->name }}
</a>

{{-- Narasi kasus --}}
<div class="card-soft p-4 my-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0">{{ $case->title }}</h1>
        @include('partials.status-badge', ['status' => $status])
    </div>

    <h2 class="h6 fw-bold text-primary">Narasi Kasus</h2>
    <p style="white-space: pre-line;">{{ $case->narrative }}</p>

    @if ($case->supporting_info)
        <h2 class="h6 fw-bold text-primary">Informasi Pendukung</h2>
        <p style="white-space: pre-line;">{{ $case->supporting_info }}</p>
    @endif

    @if ($case->instructions)
        <h2 class="h6 fw-bold text-primary">Instruksi Pengerjaan</h2>
        <p class="mb-0" style="white-space: pre-line;">{{ $case->instructions }}</p>
    @endif
</div>

@error('jawaban')
    <div class="alert alert-danger border-0 rounded-4 shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i>{{ $message }}</div>
@enderror
@error('feedback')
    <div class="alert alert-danger border-0 rounded-4 shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i>{{ $message }}</div>
@enderror

{{-- Jawaban yang sudah dikirim beserta feedback --}}
@foreach ($submissions as $sub)
    @php($fb = $sub->feedback)
    <div class="card-soft p-4 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h5 fw-bold mb-0">{{ $sub->attempt_number == 1 ? 'Jawaban Pertama' : 'Jawaban Revisi' }}</h2>
            <small class="text-muted">Dikirim {{ $sub->submitted_at->translatedFormat('d M Y, H:i') }}</small>
        </div>

        @foreach ($aspects as $key => $label)
            <div class="mb-3">
                <div class="fw-semibold text-primary small text-uppercase">{{ $label }}</div>
                <div style="white-space: pre-line;">{{ $sub->{'jawaban_' . $key} }}</div>
            </div>
        @endforeach

        <hr>
        <h3 class="h6 fw-bold"><i class="bi bi-stars me-1"></i>Feedback AI</h3>

        @if ($fb?->status === 'success')
            @foreach ($aspects as $key => $label)
                <div class="bg-light rounded-4 p-3 mb-2">
                    <div class="fw-semibold small mb-1">Feedback {{ $label }}</div>
                    <div style="white-space: pre-line;">{{ $fb->{'feedback_' . $key} }}</div>
                </div>
            @endforeach
        @else
            <div class="alert alert-{{ $fb?->status === 'failed' ? 'warning' : 'info' }} border-0 rounded-4 mb-0">
                <p class="mb-2">
                    @if ($fb?->status === 'failed')
                        Feedback AI belum berhasil dibuat. Jawaban Anda tetap tersimpan dan jatah revisi tidak berkurang.
                    @else
                        Feedback AI sedang diproses.
                    @endif
                </p>
                <form method="POST" action="{{ route('siswa.submissions.retry', $sub) }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-dark rounded-pill"><i class="bi bi-arrow-repeat me-1"></i>Coba Minta Feedback Lagi</button>
                </form>
            </div>
        @endif
    </div>
@endforeach

{{-- Pesan sesuai tahap --}}
@if ($mode === 'menunggu_feedback')
    <div class="alert alert-info border-0 rounded-4">
        <i class="bi bi-info-circle me-2"></i>Revisi dibuka setelah feedback AI berhasil diterima.
    </div>
@elseif ($mode === 'selesai')
    <div class="alert alert-info border-0 rounded-4">
        <i class="bi bi-hourglass-split me-2"></i>Jawaban Anda sudah final dan menunggu penilaian guru.
    </div>
@elseif ($mode === 'dinilai')
    <div class="alert alert-success border-0 rounded-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><i class="bi bi-patch-check me-2"></i>Jawaban Anda sudah dinilai guru.</span>
        <a href="{{ route('siswa.rooms.results.show', [$room, $case]) }}" class="btn btn-sm btn-success rounded-pill">Lihat Nilai</a>
    </div>
@endif

{{-- Form jawaban atau revisi --}}
@if ($hasForm)
    <div class="card-soft p-4 mb-4">
        <h2 class="h5 fw-bold">{{ $mode === 'revisi' ? 'Revisi Jawaban' : 'Jawaban Anda' }}</h2>

        @if ($mode === 'revisi')
            <div class="alert alert-warning border-0 rounded-4 small">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Perbaiki jawaban berdasarkan feedback di atas. Ini <strong>kesempatan revisi satu-satunya</strong> untuk kasus ini.
            </div>
        @else
            <p class="text-muted small">Isi ketiga kolom. Setelah mengirim, Anda mendapat feedback AI dan satu kali kesempatan revisi.</p>
        @endif

        <form method="POST" action="{{ route('siswa.cases.submit', $case) }}" id="answer-form">
            @csrf

            @foreach ([
                'identifikasi' => ['Identifikasi Masalah', 'Tuliskan masalah utama yang ditemukan dalam kasus.'],
                'analisis'     => ['Analisis Masalah', 'Jelaskan penyebab masalah berdasarkan informasi dalam kasus.'],
                'solusi'       => ['Solusi', 'Berikan solusi yang sesuai dengan masalah dan hasil analisis Anda.'],
            ] as $key => [$label, $hint])
                <div class="mb-3">
                    <label for="jawaban_{{ $key }}" class="form-label fw-semibold">{{ $loop->iteration }}. {{ $label }}</label>
                    <div class="form-text mt-0 mb-2">{{ $hint }}</div>
                    <textarea id="jawaban_{{ $key }}" name="jawaban_{{ $key }}" rows="5" required minlength="10" maxlength="5000"
                              class="form-control @error('jawaban_' . $key) is-invalid @enderror">{{ old('jawaban_' . $key, $latest?->{'jawaban_' . $key}) }}</textarea>
                    <div class="form-text">Minimal 10 karakter.</div>
                    @error('jawaban_' . $key) <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            @endforeach

            <button type="button" id="open-confirm" class="btn btn-accent px-4">
                {{ $mode === 'revisi' ? 'Kirim Revisi' : 'Kirim Jawaban' }}
            </button>
        </form>
    </div>

    {{-- Jendela konfirmasi: siswa memeriksa jawaban sebelum dikirim --}}
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0">
                    <h2 class="modal-title h5 fw-bold" id="confirmModalLabel">Periksa jawaban Anda</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-{{ $mode === 'revisi' ? 'warning' : 'info' }} small">
                        @if ($mode === 'revisi')
                            Setelah revisi dikirim, jawaban <strong>tidak bisa diubah lagi</strong> dan langsung menunggu penilaian guru.
                        @else
                            Setelah dikirim, jawaban pertama tidak bisa diedit. Anda masih punya 1 kali kesempatan revisi.
                        @endif
                    </div>
                    @foreach ($aspects as $key => $label)
                        <div class="mb-3">
                            <div class="fw-semibold text-primary small text-uppercase">{{ $label }}</div>
                            <div id="preview-{{ $key }}" style="white-space: pre-line;"></div>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Kembali Periksa</button>
                    <button type="button" class="btn btn-accent px-4" id="confirm-send">Ya, Kirim</button>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@if ($hasForm)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('answer-form');
    const modal = window.bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmModal'));
    const keys = ['identifikasi', 'analisis', 'solusi'];

    document.getElementById('open-confirm').addEventListener('click', function () {
        if (!form.reportValidity()) return;

        keys.forEach(function (key) {
            // textContent menjaga isi jawaban tampil apa adanya (tidak dijalankan sebagai HTML)
            document.getElementById('preview-' + key).textContent = form.elements['jawaban_' + key].value;
        });
        modal.show();
    });

    document.getElementById('confirm-send').addEventListener('click', function () {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
        form.submit();
    });
});
</script>
@endpush
@endif