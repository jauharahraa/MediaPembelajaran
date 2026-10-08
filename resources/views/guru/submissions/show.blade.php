@extends('layouts.app')
@section('title', 'Periksa Kuis')

@section('content')
@php
    $aspects = [
        'identifikasi' => 'Identifikasi Masalah',
        'analisis'     => 'Analisis Masalah',
        'solusi'       => 'Solusi',
    ];
@endphp

<a href="{{ route('guru.submissions.index') }}" class="text-decoration-none small">
    <i class="bi bi-arrow-left me-1"></i>Hasil Pengerjaan
</a>

<div class="card-soft p-4 my-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
        <div>
            <h1 class="h4 fw-bold mb-1">{{ $student->name }}</h1>
            <p class="text-muted mb-0">{{ $student->kelas }} · {{ $room->name }} · {{ $quiz['answered'] }} dari {{ $quiz['total'] }} soal dikerjakan</p>
        </div>
        <div class="text-md-end">
            @if ($quiz['complete'])
                <span class="badge text-bg-success fs-6">Sudah Dinilai · {{ number_format($quiz['finalScore'], 2, ',', '.') }}</span>
            @else
                <span class="badge text-bg-warning fs-6">Belum Dinilai Lengkap ({{ $quiz['graded'] }} dari {{ $quiz['total'] }})</span>
            @endif
        </div>
    </div>
</div>

@if ($awaitingRevision && ! $quiz['complete'])
    <div class="alert alert-info border-0 rounded-4 small">
        <i class="bi bi-info-circle me-1"></i>
        Siswa masih punya kesempatan revisi pada sebagian soal. <strong>Setelah nilai disimpan, kesempatan revisi ditutup.</strong>
    </div>
@endif

@error('versi')
    <div class="alert alert-warning border-0 rounded-4"><i class="bi bi-exclamation-triangle me-2"></i>{{ $message }}</div>
@enderror
@include('partials.form-errors')

<form method="POST" action="{{ route('guru.submissions.assess', [$room, $student]) }}" id="assess-form">
    @csrf

    @foreach ($quiz['items'] as $item)
        @php
            $case = $item['case'];
            $cid = $case->id;
        @endphp

        <div class="card-soft p-4 mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <h2 class="h5 fw-bold mb-0"><span class="badge text-bg-primary me-2">{{ $item['number'] }}</span>{{ $case->title }}</h2>
                @if ($item['assessment'])
                    <span class="badge text-bg-success">Dinilai · {{ number_format($item['assessment']->final_score, 2, ',', '.') }}</span>
                @elseif ($item['latest'])
                    <span class="badge text-bg-warning">Belum dinilai</span>
                @endif
            </div>

            @if (! $item['latest'])
                <p class="text-muted mb-0">Siswa belum mengerjakan soal ini, jadi belum bisa dinilai.</p>
            @else
                <input type="hidden" name="latest[{{ $cid }}]" value="{{ $item['latest']->id }}">

                <button class="btn btn-sm btn-outline-secondary rounded-pill" type="button"
                        data-bs-toggle="collapse" data-bs-target="#info-{{ $cid }}">
                    <i class="bi bi-card-text me-1"></i>Narasi kasus dan rubrik
                </button>
                <button class="btn btn-sm btn-outline-secondary rounded-pill" type="button"
                        data-bs-toggle="collapse" data-bs-target="#fb-{{ $cid }}">
                    <i class="bi bi-stars me-1"></i>Feedback AI
                </button>

                <div class="collapse mt-3" id="info-{{ $cid }}">
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

                    <h3 class="h6 fw-bold text-primary">Rubrik</h3>
                    @if ($case->rubric)
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead><tr><th>Aspek</th><th>Indikator</th><th>Kriteria jawaban</th></tr></thead>
                                <tbody>
                                @foreach ($aspects as $key => $label)
                                    <tr>
                                        <td class="fw-semibold">{{ $label }}</td>
                                        <td style="white-space: pre-line;">{{ $case->rubric->{$key . '_indikator'} }}</td>
                                        <td style="white-space: pre-line;">{{ $case->rubric->{$key . '_kriteria'} }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-warning mb-0">Rubrik kasus ini belum diisi.</p>
                    @endif
                </div>

                {{-- Jawaban pertama dan revisi berdampingan --}}
                @foreach ($aspects as $key => $label)
                    <div class="mt-3">
                        <div class="fw-semibold text-primary small text-uppercase mb-1">{{ $label }}</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="small text-muted mb-1">
                                    Jawaban pertama
                                    @if ($item['latest']->id === $item['first']->id) <span class="badge text-bg-success ms-1">Dinilai</span> @endif
                                </div>
                                <div class="border rounded-3 p-3 h-100" style="white-space: pre-line;">{{ $item['first']->{'jawaban_' . $key} }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted mb-1">
                                    Jawaban revisi
                                    @if ($item['second']) <span class="badge text-bg-success ms-1">Dinilai</span> @endif
                                </div>
                                @if ($item['second'])
                                    <div class="border rounded-3 p-3 h-100" style="white-space: pre-line;">{{ $item['second']->{'jawaban_' . $key} }}</div>
                                @else
                                    <div class="border rounded-3 p-3 h-100 text-muted">Siswa belum mengirim revisi.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="collapse mt-3" id="fb-{{ $cid }}">
                    @foreach ($aspects as $key => $label)
                        <div class="fw-semibold small mb-1">Feedback AI: {{ $label }}</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Untuk jawaban pertama</div>
                                <div class="bg-light rounded-3 p-3 h-100" style="white-space: pre-line;">{{ $item['first']->feedback?->status === 'success' ? $item['first']->feedback->{'feedback_' . $key} : 'Feedback AI belum tersedia.' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Untuk revisi</div>
                                <div class="bg-light rounded-3 p-3 h-100" style="white-space: pre-line;">{{ $item['second'] ? ($item['second']->feedback?->status === 'success' ? $item['second']->feedback->{'feedback_' . $key} : 'Feedback AI belum tersedia.') : 'Belum ada revisi.' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Penilaian soal ini --}}
                <div class="score-group border-top pt-3 mt-3" data-case="{{ $cid }}">
                    <div class="row g-3">
                        @foreach ($aspects as $key => $label)
                            <div class="col-md-4">
                                <label for="n-{{ $cid }}-{{ $key }}" class="form-label fw-semibold small">Nilai {{ $label }} (0 sampai 100)</label>
                                <input id="n-{{ $cid }}-{{ $key }}" type="number" name="nilai[{{ $cid }}][{{ $key }}]"
                                       min="0" max="100" step="1" required
                                       value="{{ old('nilai.' . $cid . '.' . $key, $item['assessment']?->{'nilai_' . $key}) }}"
                                       class="form-control score-input @error('nilai.' . $cid . '.' . $key) is-invalid @enderror">
                                @error('nilai.' . $cid . '.' . $key)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="small text-muted mt-2">Nilai soal ini: <strong class="case-avg text-body">-</strong></div>

                    <label for="k-{{ $cid }}" class="form-label fw-semibold small mt-3">Komentar untuk soal ini (opsional, dapat dibaca siswa)</label>
                    <textarea id="k-{{ $cid }}" name="komentar[{{ $cid }}]" rows="2" maxlength="2000"
                              class="form-control @error('komentar.' . $cid) is-invalid @enderror">{{ old('komentar.' . $cid, $item['assessment']?->comment) }}</textarea>
                    @error('komentar.' . $cid)
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endif
        </div>
    @endforeach

    <div class="card-soft p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h5 fw-bold mb-1">Nilai Akhir Kuis</h2>
                <div class="small text-muted">Rata-rata nilai semua soal. Angka resmi dihitung ulang oleh server saat disimpan.</div>
            </div>
            <div class="display-6 fw-bold text-primary" id="final-preview" data-total="{{ $quiz['total'] }}">-</div>
        </div>
        <button class="btn btn-accent px-4 mt-3">{{ $quiz['graded'] > 0 ? 'Perbarui Nilai' : 'Simpan Nilai' }}</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const groups = Array.from(document.querySelectorAll('.score-group'));
    const overall = document.getElementById('final-preview');
    const total = Number(overall.dataset.total);

    function fmt(n) {
        return n.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function update() {
        const caseScores = [];

        groups.forEach(function (group) {
            const values = Array.from(group.querySelectorAll('.score-input'))
                .map(function (i) { return i.value === '' ? null : Number(i.value); });
            const out = group.querySelector('.case-avg');

            if (values.some(function (v) { return v === null || isNaN(v); })) {
                out.textContent = '-';
                return;
            }

            // Dibulatkan per soal, sama seperti yang disimpan server
            const avg = Math.round(values.reduce(function (a, b) { return a + b; }, 0) / values.length * 100) / 100;
            out.textContent = fmt(avg);
            caseScores.push(avg);
        });

        // Nilai akhir hanya muncul jika semua soal di room ini sudah terisi nilainya
        overall.textContent = (total > 0 && caseScores.length === total)
            ? fmt(Math.round(caseScores.reduce(function (a, b) { return a + b; }, 0) / total * 100) / 100)
            : '-';
    }

    document.getElementById('assess-form').addEventListener('input', update);
    update();
});
</script>
@endpush