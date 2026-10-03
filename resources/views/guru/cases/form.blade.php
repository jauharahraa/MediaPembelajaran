@extends('layouts.app')
@section('title', $case->exists ? 'Edit Kasus' : 'Tambah Kasus')

@section('content')
<a href="{{ route('guru.rooms.show', $room) }}" class="text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>{{ $room->name }}</a>
<h1 class="h4 fw-bold my-3">{{ $case->exists ? 'Edit Kasus' : 'Tambah Kasus' }}</h1>
@include('partials.form-errors')

<form method="POST"
      action="{{ $case->exists ? route('guru.rooms.cases.update', [$room, $case]) : route('guru.rooms.cases.store', $room) }}">
    @csrf
    @if ($case->exists) @method('PUT') @endif

    <div class="card-soft p-4 mb-3">
        <h2 class="h5 fw-bold mb-3">Data Kasus</h2>

        <div class="mb-3">
            <label for="title" class="form-label fw-semibold">Judul kasus</label>
            <input id="title" name="title" value="{{ old('title', $case->title) }}" required
                   class="form-control @error('title') is-invalid @enderror">
            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        @foreach ([
            ['narrative', 'Deskripsi atau narasi kasus', 6, true, 'Contoh: Komputer di laboratorium sekolah tidak dapat terhubung ke jaringan lokal meskipun kabel LAN sudah terpasang.'],
            ['supporting_info', 'Informasi pendukung (opsional)', 3, false, 'Contoh: hasil ping, konfigurasi IP, topologi jaringan.'],
            ['instructions', 'Instruksi pengerjaan (opsional)', 3, false, 'Contoh: Identifikasi masalah, analisis penyebabnya, lalu berikan solusi.'],
        ] as [$field, $label, $rows, $required, $placeholder])
            <div class="mb-3">
                <label for="{{ $field }}" class="form-label fw-semibold">{{ $label }}</label>
                <textarea id="{{ $field }}" name="{{ $field }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}" @required($required)
                          class="form-control @error($field) is-invalid @enderror">{{ old($field, $case->$field) }}</textarea>
                @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        @endforeach
    </div>

    <div class="card-soft p-4 mb-3">
        <h2 class="h5 fw-bold mb-1">Rubrik Penilaian</h2>
        <p class="text-muted small mb-3">
            Rubrik dipakai AI sebagai acuan feedback dan dipakai Anda saat menilai. Siswa tidak melihat rubrik ini.
        </p>

        @foreach ([
            'identifikasi' => ['Aspek 1: Identifikasi Masalah', 'Contoh: Siswa mampu menyebutkan masalah utama pada kasus.', 'Contoh: Jawaban menyebut bahwa komputer tidak mendapat koneksi ke jaringan lokal.'],
            'analisis'     => ['Aspek 2: Analisis Masalah', 'Contoh: Siswa mampu menjelaskan penyebab masalah.', 'Contoh: Jawaban membahas kemungkinan penyebab seperti kabel, IP address, atau switch.'],
            'solusi'       => ['Aspek 3: Solusi', 'Contoh: Siswa mampu memberi solusi yang sesuai dengan analisisnya.', 'Contoh: Jawaban memuat langkah pengecekan dan perbaikan yang berurutan.'],
        ] as $key => [$title, $phIndikator, $phKriteria])
            <div class="border rounded-4 p-3 mb-3">
                <h3 class="h6 fw-bold text-primary">{{ $title }}</h3>

                <label for="{{ $key }}_indikator" class="form-label fw-semibold">Indikator penilaian</label>
                <textarea id="{{ $key }}_indikator" name="{{ $key }}_indikator" rows="2" placeholder="{{ $phIndikator }}" required
                          class="form-control mb-3 @error($key . '_indikator') is-invalid @enderror">{{ old($key . '_indikator', $case->rubric?->{$key . '_indikator'}) }}</textarea>
                @error($key . '_indikator') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                <label for="{{ $key }}_kriteria" class="form-label fw-semibold">Kriteria jawaban yang diharapkan</label>
                <textarea id="{{ $key }}_kriteria" name="{{ $key }}_kriteria" rows="3" placeholder="{{ $phKriteria }}" required
                          class="form-control @error($key . '_kriteria') is-invalid @enderror">{{ old($key . '_kriteria', $case->rubric?->{$key . '_kriteria'}) }}</textarea>
                @error($key . '_kriteria') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        @endforeach
    </div>

    <div class="d-flex gap-2 mb-4">
        <button class="btn btn-accent px-4">Simpan Kasus dan Rubrik</button>
        <a href="{{ route('guru.rooms.show', $room) }}" class="btn btn-light rounded-pill px-4">Batal</a>
    </div>
</form>
@endsection