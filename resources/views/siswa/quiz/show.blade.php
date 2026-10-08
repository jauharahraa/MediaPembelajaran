@extends('layouts.app')
@section('title', 'Kuis Case Method')

@section('content')
@php
    $aspects = [
        'identifikasi' => 'Identifikasi Masalah',
        'analisis'     => 'Analisis Masalah',
        'solusi'       => 'Solusi',
    ];
    $hints = [
        'identifikasi' => 'Tuliskan masalah utama yang ditemukan dalam kasus.',
        'analisis'     => 'Jelaskan penyebab masalah berdasarkan informasi dalam kasus.',
        'solusi'       => 'Berikan solusi yang sesuai dengan masalah dan hasil analisis Anda.',
    ];
    $modeBadge = [
        'pertama'           => ['Belum dijawab', 'secondary'],
        'revisi'            => ['Bisa direvisi', 'warning'],
        'menunggu_feedback' => ['Menunggu feedback AI', 'info'],
        'selesai'           => ['Menunggu penilaian guru', 'info'],
        'dinilai'           => ['Sudah dinilai', 'success'],
    ];
    $canEdit = ! $locked && $quiz['hasEditable'];
    $pertamaCount = $quiz['items']->where('mode', 'pertama')->count();
    $revisiCount = $quiz['items']->where('mode', 'revisi')->count();
    $sendLabel = $revisiCount === 0 ? 'Kirim Semua Jawaban' : ($pertamaCount === 0 ? 'Kirim Revisi' : 'Kirim Jawaban');
@endphp

<a href="{{ route('siswa.rooms.show', $room) }}#kuis" class="text-decoration-none small">
    <i class="bi bi-arrow-left me-1"></i>{{ $room->name }}
</a>

<div class="card-soft p-4 my-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
        <div>
            <h1 class="h4 fw-bold mb-1">Kuis Case Method</h1>
            <p class="text-muted mb-0">{{ $room->name }} · {{ $quiz['total'] }} soal · dikerjakan {{ $quiz['answered'] }} dari {{ $quiz['total'] }}</p>
        </div>
        @include('partials.status-badge', ['status' => $quiz['status']])
    </div>
    <hr>
    <ol class="small text-muted mb-0">
        <li>Baca setiap soal, lalu isi tiga kolom jawaban: identifikasi, analisis, dan solusi.</li>
        <li>Kirim semua jawaban sekaligus. Anda akan mendapat feedback AI untuk setiap soal.</li>
        <li>Anda punya <strong>satu kali kesempatan revisi</strong> setelah feedback diterima.</li>
        <li>Guru menilai jawaban terakhir. Nilai akhir kuis adalah rata-rata nilai semua soal.</li>
    </ol>
</div>

@if ($locked)
    <div class="alert alert-warning border-0 rounded-4"><i class="bi bi-lock me-2"></i>Room ini dinonaktifkan oleh guru. Jawaban tidak bisa dikirim sementara.</div>
@endif

@error('jawaban')
    <div class="alert alert-danger border-0 rounded-4 shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i>{{ $message }}</div>
@enderror

@error('feedback')
    <div class="alert alert-danger border-0 rounded-4 shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i>{{ $message }}</div>
@enderror

@if ($errors->has('answers') || $errors->has('answers.*'))
    <div class="alert alert-danger border-0 rounded-4 shadow-sm">Ada jawaban yang belum lengkap. Periksa kolom yang bertanda merah.</div>
@endif

@if ($quiz['feedbackIssues'] > 0)
    <div class="alert alert-warning border-0 rounded-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><i class="bi bi-stars me-2"></i>{{ $quiz['feedbackIssues'] }} feedback AI belum tersedia. Jawaban Anda aman dan jatah revisi tidak berkurang.</span>
        <form method="POST" action="{{ route('siswa.rooms.quiz.retry', $room) }}"
              onsubmit="const b = this.querySelector('button'); b.disabled = true; b.textContent = 'Memproses...';">
            @csrf
            <button class="btn btn-sm btn-dark rounded-pill"><i class="bi bi-arrow-repeat me-1"></i>Minta Feedback yang Belum Ada</button>
        </form>
    </div>
@endif

@if ($quiz['total'] === 0)
    <div class="card-soft p-4 text-muted">Guru belum membuat soal kuis di room ini.</div>
@else
    @if ($canEdit)
        <form method="POST" action="{{ route('siswa.rooms.quiz.submit', $room) }}" id="quiz-form">
            @csrf

            @if ($revisiCount > 0)
                <div class="alert alert-warning border-0 rounded-4 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Perbaiki jawaban berdasarkan feedback. Ini <strong>kesempatan revisi satu-satunya</strong>, dan setelah dikirim jawaban tidak bisa diubah lagi.
                </div>
            @endif
    @endif

    @foreach ($quiz['items'] as $item)
        @php
            $case = $item['case'];
            $cid = $case->id;
            $isForm = $canEdit && $item['editable'];
            [$badgeLabel, $badgeColor] = $modeBadge[$item['mode']];
        @endphp

        <div class="card-soft p-4 mb-4 quiz-case"
             @if ($isForm) data-editable="1" data-title="Soal {{ $item['number'] }}: {{ $case->title }}" @endif>

            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <h2 class="h5 fw-bold mb-0"><span class="badge text-bg-primary me-2">{{ $item['number'] }}</span>{{ $case->title }}</h2>
                <span class="badge text-bg-{{ $badgeColor }}">{{ $badgeLabel }}</span>
            </div>

            <h3 class="h6 fw-bold text-primary">Narasi Kasus</h3>
            <p style="white-space: pre-line;">{{ $case->narrative }}</p>
            @if ($case->supporting_info)
                <h3 class="h6 fw-bold text-primary">Informasi Pendukung</h3>
                <p style="white-space: pre-line;">{{ $case->supporting_info }}</p>
            @endif
            @if ($case->instructions)
                <h3 class="h6 fw-bold text-primary">Instruksi Pengerjaan</h3>
                <p style="white-space: pre-line;">{{ $case->instructions }}</p>
            @endif

            <hr>

            @if ($isForm)
                @if ($item['mode'] === 'revisi')
                    @include('partials.submission-detail', ['sub' => $item['latest'], 'aspects' => $aspects, 'showAnswers' => false, 'allowRetry' => false])
                    <h3 class="h6 fw-bold mt-3">Revisi Jawaban</h3>
                @else
                    <h3 class="h6 fw-bold">Jawaban Anda</h3>
                @endif

                @foreach ($aspects as $key => $label)
                    <div class="mb-3">
                        <label for="a-{{ $cid }}-{{ $key }}" class="form-label fw-semibold">{{ $loop->iteration }}. {{ $label }}</label>
                        <div class="form-text mt-0 mb-2">{{ $hints[$key] }}</div>
                        <textarea id="a-{{ $cid }}-{{ $key }}" name="answers[{{ $cid }}][jawaban_{{ $key }}]"
                                  data-label="{{ $label }}" rows="4" required minlength="10" maxlength="5000"
                                  class="form-control @error('answers.' . $cid . '.jawaban_' . $key) is-invalid @enderror">{{ old('answers.' . $cid . '.jawaban_' . $key, $item['latest']?->{'jawaban_' . $key}) }}</textarea>
                        @error('answers.' . $cid . '.jawaban_' . $key)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            @elseif (! $item['latest'])
                <p class="text-muted mb-0">Soal ini belum dijawab.</p>
            @else
                <button class="btn btn-sm btn-outline-secondary rounded-pill" type="button"
                        data-bs-toggle="collapse" data-bs-target="#detail-{{ $cid }}">
                    <i class="bi bi-chat-square-text me-1"></i>Lihat jawaban dan feedback
                </button>
                <div class="collapse {{ $item['mode'] === 'menunggu_feedback' ? 'show' : '' }} mt-3" id="detail-{{ $cid }}">
                    @foreach ($item['first'] ? [$item['first'], $item['second']] : [] as $sub)
                        @if ($sub)
                            @include('partials.submission-detail', ['sub' => $sub, 'aspects' => $aspects, 'allowRetry' => true])
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach

    @if ($canEdit)
            <div class="position-sticky bottom-0 pb-3" style="z-index: 1020;">
                <div class="card-soft border p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="small text-muted" id="progress-text"></div>
                    <button type="button" id="open-confirm" class="btn btn-accent px-4">{{ $sendLabel }}</button>
                </div>
            </div>
        </form>

        <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h2 class="modal-title h5 fw-bold" id="confirmModalLabel">Periksa jawaban Anda</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-{{ $revisiCount > 0 ? 'warning' : 'info' }} small">
                            @if ($revisiCount > 0)
                                Setelah revisi dikirim, jawaban <strong>tidak bisa diubah lagi</strong> dan langsung menunggu penilaian guru.
                            @else
                                Setelah dikirim, jawaban pertama tidak bisa diedit. Anda masih punya 1 kali kesempatan revisi.
                            @endif
                        </div>
                        <div id="preview-body"></div>
                        <p class="small text-muted mt-2 mb-0">Setelah dikirim, AI membuat feedback untuk setiap soal. Prosesnya bisa sampai 1 menit, jadi jangan tutup halaman.</p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Kembali Periksa</button>
                        <button type="button" class="btn btn-accent px-4" id="confirm-send">Ya, Kirim</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($quiz['complete'])
        <div class="alert alert-success border-0 rounded-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><i class="bi bi-patch-check me-2"></i>Kuis ini sudah dinilai guru.</span>
            <a href="{{ route('siswa.rooms.results.index', $room) }}" class="btn btn-sm btn-success rounded-pill">Lihat Nilai</a>
        </div>
    @endif
@endif

{{-- Form tersembunyi untuk tombol "Coba Minta Feedback Lagi" (tidak boleh bersarang di form kuis) --}}
@stack('forms')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('quiz-form');
    if (!form) return;

    const blocks = Array.from(form.querySelectorAll('.quiz-case[data-editable="1"]'));
    const progress = document.getElementById('progress-text');
    const modal = window.bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmModal'));
    const body = document.getElementById('preview-body');

    function filledCount() {
        return blocks.filter(function (block) {
            return Array.from(block.querySelectorAll('textarea')).every(function (t) { return t.value.trim().length >= 10; });
        }).length;
    }

    function updateProgress() {
        progress.textContent = filledCount() + ' dari ' + blocks.length + ' soal terisi lengkap';
    }
    form.addEventListener('input', updateProgress);
    updateProgress();

    document.getElementById('open-confirm').addEventListener('click', function () {
        if (!form.reportValidity()) return;

        body.replaceChildren();
        blocks.forEach(function (block) {
            const title = document.createElement('h3');
            title.className = 'h6 fw-bold';
            title.textContent = block.dataset.title;
            body.appendChild(title);

            block.querySelectorAll('textarea').forEach(function (t) {
                const label = document.createElement('div');
                label.className = 'fw-semibold text-primary small text-uppercase';
                label.textContent = t.dataset.label;

                // textContent menampilkan jawaban apa adanya, bukan sebagai HTML
                const value = document.createElement('div');
                value.className = 'mb-2';
                value.style.whiteSpace = 'pre-line';
                value.textContent = t.value;

                body.append(label, value);
            });
            body.appendChild(document.createElement('hr'));
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