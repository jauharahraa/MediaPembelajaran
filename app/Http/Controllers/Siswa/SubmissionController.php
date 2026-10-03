<?php

namespace App\Http\Controllers\Siswa;

use App\Exceptions\SubmissionException;
use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\CaseSubmission;
use App\Services\GeminiService;
use App\Services\SubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Throwable;

class SubmissionController extends Controller
{
    public function store(Request $request, CaseStudy $case, SubmissionService $service, GeminiService $gemini)
    {
        Gate::authorize('answer', $case);

        $data = $request->validate([
            'jawaban_identifikasi' => ['required', 'string', 'min:10', 'max:5000'],
            'jawaban_analisis'     => ['required', 'string', 'min:10', 'max:5000'],
            'jawaban_solusi'       => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        // Nomor percobaan ditentukan service, bukan oleh request
        try {
            $submission = $service->submit($case, $request->user(), $data);
        } catch (SubmissionException $e) {
            return back()->withErrors(['jawaban' => $e->getMessage()])->withInput();
        }

        // Gemini dipanggil SETELAH jawaban tersimpan. Kegagalan tidak menghapus jawaban.
        try {
            $gemini->generateFeedback($submission);
        } catch (Throwable $e) {
            report($e);
        }

        // Diperbarui menggunakan route 'siswa.rooms.cases.show' sesuai routes/web.php
        return redirect()
    ->route('siswa.rooms.cases.show', ['room' => $case->room_id, 'case' => $case->id])
    ->with('success', 'Jawaban berhasil dikirim.');
    }

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