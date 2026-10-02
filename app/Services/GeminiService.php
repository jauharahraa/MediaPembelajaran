<?php

namespace App\Services;

use App\Models\CaseSubmission;
use App\Models\SubmissionFeedback;

class GeminiService
{
    // SEMENTARA: diganti pemanggilan Gemini yang sebenarnya di Tahap 5
    public function generateFeedback(CaseSubmission $submission): SubmissionFeedback
    {
        $feedback = $submission->feedback;

        $feedback->update([
            'status'         => 'failed',
            'error_message'  => 'Integrasi Gemini belum diaktifkan.',
            'attempts_count' => $feedback->attempts_count + 1,
        ]);

        return $feedback;
    }
}