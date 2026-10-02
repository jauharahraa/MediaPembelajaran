@extends('layouts.app')
@section('title', 'Bergabung ke Room')

@section('content')
<h1 class="h4 fw-bold mb-4">Bergabung ke Room</h1>

<div class="card-soft p-4" style="max-width: 520px;">
    <form method="POST" action="{{ route('siswa.join.store') }}">
        @csrf
        <label for="code" class="form-label fw-semibold">Kode Room</label>
        <input id="code" name="code" value="{{ old('code') }}" maxlength="12" placeholder="Contoh: K7M2QX" required autofocus
               class="form-control form-control-lg text-uppercase @error('code') is-invalid @enderror">
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text mb-3">Kode diberikan oleh guru Anda.</div>
        <button class="btn btn-accent px-4">Gabung</button>
    </form>
</div>
@endsection