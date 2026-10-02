<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\TeacherAssessment;
use App\Models\User;
use App\Services\GradeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssessmentController extends Controller
{
    // Simpan nilai baru atau perbarui nilai yang sudah ada
    public function store(Request $request, CaseStudy $case, User $student)
    {
        Gate::authorize('manage', $case); // hanya guru pemilik room

        // Jawaban yang dinilai selalu jawaban terakhir, ditentukan server
        $latest = $case->submissions()->where('user_id', $student->id)
            ->orderByDesc('attempt_number')->firstOrFail();

        $data = $request->validate([
            'nilai_identifikasi' => ['required', 'integer', 'between:0,100'],
            'nilai_analisis'     => ['required', 'integer', 'between:0,100'],
            'nilai_solusi'       => ['required', 'integer', 'between:0,100'],
            'comment'            => ['nullable', 'string', 'max:2000'],
        ]);

        TeacherAssessment::updateOrCreate(
            ['case_id' => $case->id, 'user_id' => $student->id],
            $data + [
                'submission_id' => $latest->id,
                'final_score'   => GradeCalculator::finalScore(
                    $data['nilai_identifikasi'], $data['nilai_analisis'], $data['nilai_solusi']
                ),
                'graded_at'     => now(),
            ]
        );

        return redirect()->route('guru.submissions.show', [$case, $student])
            ->with('success', 'Nilai berhasil disimpan.');
    }
}