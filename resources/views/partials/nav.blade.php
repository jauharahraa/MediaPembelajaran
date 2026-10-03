@php($isGuru = auth()->user()->isGuru())

<a href="{{ route($isGuru ? 'guru.dashboard' : 'siswa.dashboard') }}"
   class="d-flex align-items-center gap-2 mb-4 px-2 text-decoration-none">
    <span class="brand-badge"><i class="bi bi-diagram-3-fill"></i></span>
    <span class="fw-bold text-white">{{ config('app.name') }}</span>
</a>

<nav class="nav flex-column gap-1">
    @if ($isGuru)
        <a class="nav-link {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}" href="{{ route('guru.dashboard') }}">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>
        <a class="nav-link {{ request()->routeIs('guru.rooms.*') ? 'active' : '' }}" href="{{ route('guru.rooms.index') }}">
            <i class="bi bi-door-open me-2"></i>Room Materi
        </a>
        <a class="nav-link {{ request()->routeIs('guru.submissions.*') ? 'active' : '' }}" href="{{ route('guru.submissions.index') }}">
            <i class="bi bi-journal-check me-2"></i>Hasil Pengerjaan
        </a>
    @else
        <a class="nav-link {{ request()->routeIs('siswa.dashboard') ? 'active' : '' }}" href="{{ route('siswa.dashboard') }}">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>
        <a class="nav-link {{ request()->routeIs('siswa.join*') ? 'active' : '' }}" href="{{ route('siswa.join') }}">
            <i class="bi bi-key me-2"></i>Bergabung ke Room
        </a>
        <a class="nav-link {{ request()->routeIs('siswa.rooms.*') ? 'active' : '' }}" href="{{ route('siswa.rooms.index') }}">
            <i class="bi bi-collection me-2"></i>Room Saya
        </a>
    @endif
</nav>

<div class="mt-auto pt-3 border-top border-light border-opacity-25">
    <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
        <i class="bi bi-person-circle me-2"></i>Profil Saya
    </a>
</div>