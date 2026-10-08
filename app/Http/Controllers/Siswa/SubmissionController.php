<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\CaseSubmission;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Gate;
use Throwable;

class SubmissionController extends Controller
{
    // Mengulang permintaan feedback tanpa membuat jawaban baru dan tanpa mengurangi jatah revisi
    public function retryFeedback(CaseSubmission $submission, GeminiService $gemini)
    {
        Gate::authorize('view', $submission);

        if ($submission->feedback?->status === 'success') {
            return back()->with('success', 'Feedback sudah tersedia.');
        }

        try {
            $gemini->generateFeedback($submission);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['feedback' => 'Feedback belum bisa dibuat. Coba lagi beberapa saat.']);
        }

        return back()->with('success', 'Permintaan feedback telah diproses ulang.');
    }
}