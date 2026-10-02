<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
<div class="d-lg-flex">

    {{-- Sidebar desktop --}}
    <aside class="sidebar sidebar-desktop d-none d-lg-flex flex-column p-3">
        @include('partials.nav')
    </aside>

    {{-- Sidebar ponsel/tablet --}}
    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="mobileSidebar">
        <div class="offcanvas-header justify-content-end">
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-3 pt-0">
            @include('partials.nav')
        </div>
    </div>

    <div class="flex-grow-1 main-area">
        <header class="topbar d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
            <button class="btn btn-light d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="text-end lh-sm">
                    <div class="fw-semibold">{{ auth()->user()->name }}</div>
                    <small class="text-muted">
                        {{ auth()->user()->isGuru() ? 'Guru' : 'Siswa · ' . auth()->user()->kelas }}
                    </small>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-secondary btn-sm rounded-pill">
                        <i class="bi bi-box-arrow-right me-1"></i>Keluar
                    </button>
                </form>
            </div>
        </header>

        <main class="p-3 p-lg-4">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>