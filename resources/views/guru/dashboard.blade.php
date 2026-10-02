@extends('layouts.app')
@section('title', 'Dashboard Guru')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0">Dashboard Guru</h1>
    <a href="{{ route('guru.rooms.create') }}" class="btn btn-accent"><i class="bi bi-plus-lg me-1"></i>Buat Room</a>
</div>

@php
    $cards = [
        ['Total Room', 'rooms', 'bi-door-open', 'primary'],
        ['Siswa Terdaftar', 'students', 'bi-people', 'success'],
        ['Total Kasus', 'cases', 'bi-puzzle', 'warning'],
        ['Pengerjaan Siswa', 'submissions', 'bi-journal-check', 'info'],
        ['Belum Dinilai', 'ungraded', 'bi-hourglass-split', 'danger'],
    ];
@endphp

<div class="row g-3 mb-4">
    @foreach ($cards as [$label, $key, $icon, $color])
        <div class="col-6 col-lg">
            <div class="card-soft p-3 h-100">
                <div class="stat-icon bg-{{ $color }}-subtle text-{{ $color }}-emphasis mb-2"><i class="bi {{ $icon }}"></i></div>
                <div class="fs-3 fw-bold">{{ $stats[$key] }}</div>
                <div class="text-muted small">{{ $label }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="card-soft p-3 p-lg-4">
    <h2 class="h5 fw-bold mb-3">Aktivitas Terbaru</h2>

    @if ($recent->isEmpty())
        <p class="text-muted mb-0">Belum ada pengerjaan dari siswa. Bagikan kode room kepada siswa untuk memulai.</p>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr><th>Siswa</th><th>Room</th><th>Kasus</th><th>Pengiriman</th><th>Waktu</th><th></th></tr>
                </thead>
                <tbody>
                @foreach ($recent as $sub)
                    <tr>
                        <td>{{ $sub->student->name }}</td>
                        <td>{{ $sub->case->room->name }}</td>
                        <td>{{ $sub->case->title }}</td>
                        <td>
                            <span class="badge text-bg-{{ $sub->attempt_number == 1 ? 'secondary' : 'primary' }}">
                                {{ $sub->attempt_number == 1 ? 'Jawaban pertama' : 'Revisi' }}
                            </span>
                        </td>
                        <td class="text-nowrap">{{ $sub->submitted_at->translatedFormat('d M Y, H:i') }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary rounded-pill"
                               href="{{ route('guru.submissions.show', [$sub->case_id, $sub->user_id]) }}">Lihat</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection