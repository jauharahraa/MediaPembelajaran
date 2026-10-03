@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<h1 class="h4 fw-bold mb-4">Profil Saya</h1>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-soft p-4">
            <h2 class="h5 fw-bold mb-3">Data Akun</h2>
            @include('partials.form-errors')

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Nama lengkap</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required
                           class="form-control @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input class="form-control bg-light" value="{{ $user->email }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Peran</label>
                    <div><span class="badge text-bg-primary">{{ $user->isGuru() ? 'Guru' : 'Siswa' }}</span></div>
                </div>

                @if ($user->isSiswa())
                    <div class="mb-3">
                        <label for="kelas" class="form-label fw-semibold">Kelas</label>
                        <input id="kelas" name="kelas" value="{{ old('kelas', $user->kelas) }}" required
                               class="form-control @error('kelas') is-invalid @enderror">
                        @error('kelas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                @endif

                <button class="btn btn-accent px-4">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-soft border border-danger-subtle p-4">
            <h2 class="h5 fw-bold text-danger">Hapus Akun</h2>
            <p class="text-muted">
                Akun beserta datanya akan dihapus permanen dan tidak bisa dikembalikan.
                @if ($user->isGuru())
                    Seluruh room Anda, termasuk jawaban dan nilai siswa di dalamnya, ikut terhapus.
                @endif
            </p>
            <button type="button" id="open-delete-modal" class="btn btn-outline-danger rounded-pill"
                    data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="bi bi-trash me-1"></i>Hapus Akun Saya
            </button>
        </div>
    </div>
</div>

{{-- Jendela konfirmasi --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('profile.destroy') }}" class="modal-content border-0 rounded-4">
            @csrf
            @method('DELETE')

            <div class="modal-header border-0">
                <h2 class="modal-title h5 fw-bold text-danger" id="deleteModalLabel">Hapus akun secara permanen?</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <p class="mb-2">Tindakan ini <strong>tidak bisa dibatalkan</strong>. Data berikut ikut terhapus:</p>
                <ul>
                    @foreach ($impact as $label => $total)
                        <li>{{ $label }}: <strong>{{ $total }}</strong></li>
                    @endforeach
                </ul>

                @if ($user->isGuru())
                    <div class="alert alert-warning small">
                        Siswa tidak akan bisa lagi membuka room dan nilai yang terhapus.
                    </div>
                @endif

                <label for="delete-password" class="form-label fw-semibold">Masukkan kata sandi untuk konfirmasi</label>
                <input id="delete-password" type="password" name="password" required autocomplete="current-password"
                       class="form-control {{ $errors->userDeletion->has('password') ? 'is-invalid' : '' }}">
                @if ($errors->userDeletion->has('password'))
                    <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                @endif

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="confirm-delete">
                    <label class="form-check-label" for="confirm-delete">Saya mengerti bahwa data ini tidak bisa dikembalikan.</label>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-danger rounded-pill px-4" id="delete-submit" disabled>Hapus Akun</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const confirmBox = document.getElementById('confirm-delete');
    const submitBtn = document.getElementById('delete-submit');
    confirmBox.addEventListener('change', () => { submitBtn.disabled = !confirmBox.checked; });

    @if ($errors->userDeletion->any())
        // Buka kembali jendela konfirmasi jika kata sandi salah
        document.addEventListener('DOMContentLoaded', () => document.getElementById('open-delete-modal').click());
    @endif
</script>
@endpush