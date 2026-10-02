@extends('layouts.guest')
@section('title', 'Masuk')

@section('content')
<div class="auth-wrap d-flex align-items-center justify-content-center p-3">
    <div class="card-soft p-4 p-md-5 w-100" style="max-width: 440px;">
        <a href="{{ route('landing') }}" class="d-flex align-items-center gap-2 text-decoration-none mb-4">
            <span class="brand-badge"><i class="bi bi-diagram-3-fill"></i></span>
            <span class="fw-bold">{{ config('app.name') }}</span>
        </a>
        <h1 class="h4 fw-bold">Masuk</h1>
        <p class="text-muted">Silakan masuk dengan akun Anda.</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus required
                       class="form-control form-control-lg @error('email') is-invalid @enderror">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Kata sandi</label>
                <input id="password" type="password" name="password" autocomplete="current-password" required
                       class="form-control form-control-lg @error('password') is-invalid @enderror">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <button class="btn btn-accent btn-lg w-100">Masuk</button>
        </form>

        <p class="text-center text-muted mt-4 mb-0">
            Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
        </p>
    </div>
</div>
@endsection