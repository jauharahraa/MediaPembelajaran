<div class="mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <h3 class="h6 fw-bold mb-0">{{ $sub->attempt_number == 1 ? 'Jawaban Pertama' : 'Jawaban Revisi' }}</h3>
        <small class="text-muted">Dikirim {{ $sub->submitted_at->translatedFormat('d M Y, H:i') }}</small>
    </div>

    @if ($showAnswers ?? true)
        @foreach ($aspects as $key => $label)
            <div class="mb-2">
                <div class="fw-semibold text-primary small text-uppercase">{{ $label }}</div>
                <div style="white-space: pre-line;">{{ $sub->{'jawaban_' . $key} }}</div>
            </div>
        @endforeach
    @endif

    <div class="fw-semibold small mt-3 mb-2"><i class="bi bi-stars me-1"></i>Feedback AI</div>

    @if ($sub->feedback?->status === 'success')
        @foreach ($aspects as $key => $label)
            <div class="bg-light rounded-4 p-3 mb-2">
                <div class="fw-semibold small mb-1">Feedback {{ $label }}</div>
                <div style="white-space: pre-line;">{{ $sub->feedback->{'feedback_' . $key} }}</div>
            </div>
        @endforeach
    @else
        <div class="alert alert-{{ $sub->feedback?->status === 'failed' ? 'warning' : 'info' }} border-0 rounded-4 mb-0">
            <p class="mb-2">
                @if ($sub->feedback?->status === 'failed')
                    Feedback AI belum berhasil dibuat. Jawaban Anda tetap tersimpan dan jatah revisi tidak berkurang.
                @else
                    Feedback AI belum tersedia.
                @endif
            </p>
            @if ($allowRetry ?? false)
                <button type="submit" form="retry-{{ $sub->id }}" class="btn btn-sm btn-outline-dark rounded-pill">
                    <i class="bi bi-arrow-repeat me-1"></i>Coba Minta Feedback Lagi
                </button>
                @push('forms')
                    <form id="retry-{{ $sub->id }}" method="POST" action="{{ route('siswa.submissions.retry', $sub) }}">@csrf</form>
                @endpush
            @endif
        </div>
    @endif
</div>