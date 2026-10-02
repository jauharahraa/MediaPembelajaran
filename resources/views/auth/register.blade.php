@extends('layouts.guest')
@section('title', 'Daftar')

@section('content')
<div class="auth-wrap d-flex align-items-center justify-content-center p-3">
    <div class="card-soft p-4 p-md-5 w-100 my-3" style="max-width: 500px;">
        <a href="{{ route('landing') }}" class="d-flex align-items-center gap-2 text-decoration-none mb-4">
            <span class="brand-badge"><i class="bi bi-diagram-3-fill"></i></span>
            <span class="fw-bold">{{ config('app.name') }}</span>
        </a>
        <h1 class="h4 fw-bold">Buat Akun</h1>
        <p class="text-muted">Daftar sebagai siswa atau guru.</p>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
                <label for="role" class="form-label fw-semibold">Daftar sebagai</label>
                <select id="role" name="role" class="form-select form-select-lg @error('role') is-invalid @enderror">
                    <option value="siswa" @selected(old('role', 'siswa') === 'siswa')>Siswa</option>
                    <option value="guru" @selected(old('role') === 'guru')>Guru</option>
                </select>
                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">Nama lengkap</label>
                <input id="name" name="name" value="{{ old('name') }}" required
                       class="form-control form-control-lg @error('name') is-invalid @enderror">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       class="form-control form-control-lg @error('email') is-invalid @enderror">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3" id="group-siswa">
                <label for="kelas" class="form-label fw-semibold">Kelas</label>
                <input id="kelas" name="kelas" value="{{ old('kelas') }}" placeholder="Contoh: XI TKJ 1"
                       class="form-control form-control-lg @error('kelas') is-invalid @enderror">
                @error('kelas') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3 d-none" id="group-guru">
                <label for="invite_code" class="form-label fw-semibold">Kode undangan guru</label>
                <input id="invite_code" name="invite_code" value="{{ old('invite_code') }}"
                       class="form-control form-control-lg @error('invite_code') is-invalid @enderror">
                @error('invite_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">Kode diberikan oleh pengelola sistem.</div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Kata sandi</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="form-control form-control-lg @error('password') is-invalid @enderror">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">Minimal 8 karakter.</div>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-semibold">Ulangi kata sandi</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="form-control form-control-lg">
            </div>

            <button class="btn btn-accent btn-lg w-100">Daftar</button>
        </form>

        <p class="text-center text-muted mt-4 mb-0">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const role = document.getElementById('role');
    function toggleRoleFields() {
        const isGuru = role.value === 'guru';
        document.getElementById('group-guru').classList.toggle('d-none', !isGuru);
        document.getElementById('group-siswa').classList.toggle('d-none', isGuru);
    }
    role.addEventListener('change', toggleRoleFields);
    toggleRoleFields();
</script>
@endpush