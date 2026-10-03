@php
    $map = [
        'Belum Dikerjakan'        => 'secondary',
        'Menunggu Revisi'         => 'warning',
        'Menunggu Penilaian Guru' => 'info',
        'Sudah Dinilai'           => 'success',
    ];
@endphp
<span class="badge text-bg-{{ $map[$status] ?? 'secondary' }}">{{ $status }}</span>