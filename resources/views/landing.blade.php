@extends('layouts.guest')
@section('title', config('app.name'))

@section('content')
<section class="hero">
    <nav class="container py-3 d-flex align-items-center justify-content-between">
        <a href="{{ route('landing') }}" class="d-flex align-items-center gap-2 text-white text-decoration-none fw-bold fs-5">
            <span class="brand-badge"><i class="bi bi-diagram-3-fill"></i></span>{{ config('app.name') }}
        </a>
        <div class="d-flex align-items-center gap-2">
            @auth
                <a href="{{ route(auth()->user()->isGuru() ? 'guru.dashboard' : 'siswa.dashboard') }}" class="btn btn-accent px-4">Ke Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-link text-white text-decoration-none">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-accent px-4">Daftar</a>
            @endauth
        </div>
    </nav>

    <div class="container">@include('partials.flash')</div>
    
    <div class="container pt-4 pt-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <p class="text-warning fw-semibold fst-italic mb-2">Untuk siswa kelas XI TKJ · SMK Negeri 13 Medan</p>
                <h1 class="display-5 fw-bold mb-3">
                    Asah kemampuan komputer dan jaringan lewat <span class="hero-underline">studi kasus</span>
                </h1>
                <p class="lead opacity-75 mb-4">
                    Pelajari materi dan video, pecahkan kasus jaringan, terima feedback dari AI,
                    perbaiki jawabanmu, lalu lihat nilai dari gurumu.
                </p>
                @guest
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('register') }}" class="btn btn-accent btn-lg px-4">Mulai Belajar</a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg rounded-pill px-4">Masuk</a>
                    </div>
                @endguest
            </div>

            <div class="col-lg-5">
                <div class="card-soft p-4 text-body">
                    <span class="badge text-bg-warning mb-2">Contoh kasus</span>
                    <p class="fw-semibold mb-3">
                        Komputer di laboratorium sekolah tidak dapat terhubung ke jaringan lokal
                        meskipun kabel LAN sudah terpasang.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge rounded-pill text-bg-primary">1. Identifikasi Masalah</span>
                        <span class="badge rounded-pill text-bg-primary">2. Analisis Masalah</span>
                        <span class="badge rounded-pill text-bg-primary">3. Solusi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container feature-wrap">
    <div class="row g-3">
        @foreach ([
            ['bi-journal-text', 'Materi Terstruktur', 'Satu room untuk setiap materi, dibuat langsung oleh gurumu.'],
            ['bi-play-circle', 'Video Pembelajaran', 'Tonton video langsung di dalam website, tanpa pindah halaman.'],
            ['bi-puzzle', 'Kuis Case Method', 'Kasus nyata dengan tiga kolom jawaban: identifikasi, analisis, solusi.'],
            ['bi-stars', 'Feedback AI', 'Feedback membangun dari AI, sedangkan nilai tetap ditentukan guru.'],
        ] as [$icon, $title, $desc])
            <div class="col-6 col-lg-3">
                <div class="card-soft p-3 p-lg-4 h-100 text-center">
                    <div class="stat-icon bg-primary-subtle text-primary mb-3"><i class="bi {{ $icon }}"></i></div>
                    <h2 class="h6 fw-bold">{{ $title }}</h2>
                    <p class="text-muted small mb-0">{{ $desc }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

<section class="container py-5 my-4">
    <h2 class="fw-bold text-center mb-4">Bagaimana cara belajarnya?</h2>
    <div class="row g-3">
        @foreach ([
            ['Gabung room', 'Masukkan kode room dari gurumu.'],
            ['Pelajari materi', 'Baca materi dan tonton video pembelajaran.'],
            ['Kerjakan kasus', 'Isi tiga kolom jawaban lalu kirim.'],
            ['Revisi dan lihat nilai', 'Perbaiki jawaban satu kali berdasarkan feedback AI, lalu tunggu nilai dari guru.'],
        ] as $i => [$title, $desc])
            <div class="col-md-6 col-lg-3">
                <div class="card-soft p-4 h-100">
                    <span class="step-number mb-3">{{ $i + 1 }}</span>
                    <h3 class="h6 fw-bold">{{ $title }}</h3>
                    <p class="text-muted small mb-0">{{ $desc }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

<footer class="text-center text-muted small pb-4">
    {{ config('app.name') }} · Media pembelajaran Komputer dan Jaringan Dasar · SMK Negeri 13 Medan
</footer>
@endsection