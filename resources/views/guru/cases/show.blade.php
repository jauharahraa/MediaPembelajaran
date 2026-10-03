@extends('layouts.app')
@section('title', $case->title)

@section('content')
<a href="{{ route('guru.rooms.show', $room) }}" class="text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>{{ $room->name }}</a>

<div class="card-soft p-4 my-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0">{{ $case->title }}</h1>
        <a href="{{ route('guru.rooms.cases.edit', [$room, $case]) }}" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-pencil me-1"></i>Edit</a>
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

<div class="card-soft p-4">
    <h2 class="h5 fw-bold mb-3">Rubrik Penilaian</h2>

    @if ($case->rubric)
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Aspek</th><th>Indikator</th><th>Kriteria jawaban</th></tr></thead>
                <tbody>
                @foreach (['identifikasi' => 'Identifikasi Masalah', 'analisis' => 'Analisis Masalah', 'solusi' => 'Solusi'] as $key => $label)
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
        <div class="alert alert-warning mb-0">
            Rubrik kasus ini belum diisi.
            <a href="{{ route('guru.rooms.cases.edit', [$room, $case]) }}">Isi rubrik sekarang</a>.
        </div>
    @endif
</div>
@endsection