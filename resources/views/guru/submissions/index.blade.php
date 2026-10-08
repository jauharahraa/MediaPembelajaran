@extends('layouts.app')
@section('title', 'Hasil Pengerjaan')

@section('content')
<h1 class="h4 fw-bold mb-4">Hasil Pengerjaan Siswa</h1>

<form method="GET" class="card-soft p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label for="q" class="form-label small fw-semibold">Cari siswa</label>
            <input id="q" name="q" value="{{ request('q') }}" placeholder="Nama siswa" class="form-control">
        </div>
        <div class="col-md-3">
            <label for="room_id" class="form-label small fw-semibold">Room</label>
            <select id="room_id" name="room_id" class="form-select">
                <option value="">Semua room</option>
                @foreach ($rooms as $room)
                    <option value="{{ $room->id }}" @selected(request('room_id') == $room->id)>{{ $room->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="status" class="form-label small fw-semibold">Status penilaian</label>
            <select id="status" name="status" class="form-select">
                <option value="">Semua</option>
                <option value="belum" @selected(request('status') === 'belum')>Belum dinilai lengkap</option>
                <option value="sudah" @selected(request('status') === 'sudah')>Sudah dinilai</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-accent flex-grow-1">Terapkan</button>
            <a href="{{ route('guru.submissions.index') }}" class="btn btn-light rounded-pill" title="Reset filter"><i class="bi bi-x-lg"></i></a>
        </div>
    </div>
</form>

<div class="card-soft p-3 p-lg-4">
    @if ($rows->isEmpty())
        <p class="text-muted mb-0">Belum ada pengerjaan yang sesuai. Pengerjaan muncul setelah siswa mengirim jawaban.</p>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Siswa</th><th>Kelas</th><th>Room</th><th>Soal dijawab</th>
                        <th>Percobaan</th><th>Terakhir kirim</th><th>Status</th><th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td class="fw-semibold">{{ $row->student_name }}</td>
                        <td>{{ $row->kelas }}</td>
                        <td>{{ $row->room_name }}</td>
                        <td>{{ $row->answered }} dari {{ $row->total_cases }}</td>
                        <td>
                            <span class="badge text-bg-{{ $row->attempts == 1 ? 'secondary' : 'primary' }}">{{ $row->attempts }} dari 2</span>
                        </td>
                        <td class="text-nowrap">{{ $row->last_submitted_at->translatedFormat('d M Y, H:i') }}</td>
                        <td>
                            @if ($row->complete)
                                <span class="badge text-bg-success">Sudah Dinilai · {{ number_format($row->avg_score, 2, ',', '.') }}</span>
                            @elseif ($row->graded > 0)
                                <span class="badge text-bg-info">Dinilai {{ $row->graded }} dari {{ $row->total_cases }}</span>
                            @else
                                <span class="badge text-bg-warning">Belum Dinilai</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('guru.submissions.show', [$row->room_id, $row->student_id]) }}"
                               class="btn btn-sm {{ $row->complete ? 'btn-outline-primary' : 'btn-accent' }} rounded-pill">
                                {{ $row->complete ? 'Lihat' : 'Periksa' }}
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $rows->links() }}</div>
    @endif
</div>
@endsection