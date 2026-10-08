@extends('layouts.app')
@section('title', 'Hasil Kuis')

@section('content')
@php
    $aspects = [
        'identifikasi' => 'Identifikasi Masalah',
        'analisis'     => 'Analisis Masalah',
        'solusi'       => 'Solusi',
    ];
@endphp

<a href="{{ route('siswa.rooms.show', $room) }}#hasil" class="text-decoration-none small">
    <i class="bi bi-arrow-left me-1"></i>{{ $room->name }}
</a>

<div class="card-soft p-4 my-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="text-muted small">{{ $room->name }}</div>
            <h1 class="h4 fw-bold mb-2">Hasil Pengerjaan Kuis</h1>
            @include('partials.status-badge', ['status' => $quiz['status']])
        </div>
        <div class="text-end">
            @if ($quiz['complete'])
                <div class="small text-muted">Nilai Akhir Kuis</div>
                <div class="display-5 fw-bold text-primary lh-1">{{ number_format($quiz['finalScore'], 2, ',', '.') }}</div>
            @else
                <div class="text-muted"><i class="bi bi-hourglass-split me-1"></i>Menunggu Penilaian Guru</div>
                <div class="small text-muted">{{ $quiz['graded'] }} dari {{ $quiz['total'] }} soal sudah dinilai</div>
            @endif
        </div>
    </div>
</div>

<div class="card-soft p-4 mb-3">
    <h2 class="h5 fw-bold mb-3">Rincian Nilai</h2>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Soal</th>
                    <th class="text-end">Identifikasi</th>
                    <th class="text-end">Analisis</th>
                    <th class="text-end">Solusi</th>
                    <th class="text-end">Nilai Soal</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($quiz['items'] as $item)
                <tr>
                    <td>Soal {{ $item['number'] }}: {{ $item['case']->title }}</td>
                    @if ($item['assessment'])
                        <td class="text-end">{{ $item['assessment']->nilai_identifikasi }}</td>
                        <td class="text-end">{{ $item['assessment']->nilai_analisis }}</td>
                        <td class="text-end">{{ $item['assessment']->nilai_solusi }}</td>
                        <td class="text-end fw-bold">{{ number_format($item['assessment']->final_score, 2, ',', '.') }}</td>
                    @else
                        <td colspan="4" class="text-end text-muted">{{ $item['latest'] ? 'Belum dinilai' : 'Belum dikerjakan' }}</td>
                    @endif
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr class="table-primary fw-bold">
                    <td colspan="4">Nilai Akhir Kuis</td>
                    <td class="text-end">{{ $quiz['complete'] ? number_format($quiz['finalScore'], 2, ',', '.') : '-' }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<h2 class="h5 fw-bold mb-3">Jawaban, Feedback, dan Komentar Guru</h2>
@foreach ($quiz['items'] as $item)
    <div class="card-soft p-3 p-lg-4 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h3 class="h6 fw-bold mb-0">Soal {{ $item['number'] }}: {{ $item['case']->title }}</h3>
            @if ($item['latest'])
                <button class="btn btn-sm btn-outline-secondary rounded-pill" type="button"
                        data-bs-toggle="collapse" data-bs-target="#res-{{ $item['case']->id }}">Lihat detail</button>
            @else
                <a href="{{ route('siswa.rooms.quiz', $room) }}" class="btn btn-sm btn-accent rounded-pill">Kerjakan</a>
            @endif
        </div>

        @if ($item['latest'])
            <div class="collapse mt-3" id="res-{{ $item['case']->id }}">
                @if ($item['assessment'])
                    <h4 class="h6 fw-bold">Komentar Guru</h4>
                    @if ($item['assessment']->comment)
                        <div class="bg-light rounded-4 p-3 mb-3" style="white-space: pre-line;">{{ $item['assessment']->comment }}</div>
                    @else
                        <p class="text-muted">Guru tidak menambahkan komentar.</p>
                    @endif
                @endif

                @include('partials.submission-detail', ['sub' => $item['latest'], 'aspects' => $aspects])
            </div>
        @endif
    </div>
@endforeach
@endsection