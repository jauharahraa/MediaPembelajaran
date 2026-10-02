<?php

namespace App\Services;

use App\Exceptions\SubmissionException;
use App\Models\CaseStudy;
use App\Models\CaseSubmission;
use App\Models\SubmissionFeedback;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubmissionService
{
    /**
     * Menyimpan jawaban pertama atau revisi. Nomor percobaan ditentukan
     * oleh server, bukan oleh request dari browser.
     */
    public function submit(CaseStudy $case, User $student, array $answers): CaseSubmission
    {
        return DB::transaction(function () use ($case, $student, $answers) {
            // Kunci baris kasus agar dua request bersamaan diproses satu per satu
            CaseStudy::whereKey($case->id)->lockForUpdate()->first();

            $existing = CaseSubmission::where('case_id', $case->id)
                ->where('user_id', $student->id)
                ->orderBy('attempt_number')
                ->get();

            if ($case->assessments()->where('user_id', $student->id)->exists()) {
                throw new SubmissionException('Jawaban sudah dinilai guru, tidak bisa diubah lagi.');
            }

            $maxAttempts = (int) config('casemethod.max_attempts', 2);

            if ($existing->count() >= $maxAttempts) {
                throw new SubmissionException('Kesempatan revisi untuk kasus ini sudah habis.');
            }

            if ($existing->count() === 1) {
                $first = $existing->first()->feedback;
                if (! $first || $first->status !== 'success') {
                    throw new SubmissionException('Revisi dapat dikirim setelah feedback pertama berhasil diterima.');
                }
            }

            $submission = CaseSubmission::create([
                'case_id'              => $case->id,
                'user_id'              => $student->id,
                'attempt_number'       => $existing->count() + 1,
                'jawaban_identifikasi' => $answers['jawaban_identifikasi'],
                'jawaban_analisis'     => $answers['jawaban_analisis'],
                'jawaban_solusi'       => $answers['jawaban_solusi'],
                'submitted_at'         => now(),
            ]);

            // Baris feedback dibuat sekarang; Gemini mengisinya di Tahap 5
            SubmissionFeedback::create([
                'submission_id' => $submission->id,
                'status'        => 'pending',
            ]);

            return $submission;
        });
    }
}