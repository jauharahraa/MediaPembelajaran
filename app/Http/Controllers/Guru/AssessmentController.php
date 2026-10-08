<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\TeacherAssessment;
use App\Models\User;
use App\Services\GradeCalculator;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AssessmentController extends Controller
{
    private const ASPECTS = ['identifikasi', 'analisis', 'solusi'];

    // Menyimpan atau memperbarui nilai semua soal milik satu siswa sekaligus
    public function store(Request $request, Room $room, User $student, QuizService $quizService)
    {
        Gate::authorize('manage', $room);
        abort_unless($student->isSiswa() && $room->hasMember($student), 404);

        $quiz = $quizService->build($room, $student);
        $gradable = $quiz['items']->filter(fn ($i) => $i['latest'] !== null); // soal yang sudah dijawab
        abort_if($gradable->isEmpty(), 404);

        // Jika siswa mengirim revisi saat guru sedang menilai, nilai tidak boleh menempel ke jawaban yang salah
        foreach ($gradable as $item) {
            $seen = (int) $request->input('latest.' . $item['case']->id);

            if ($seen !== $item['latest']->id) {
                return back()->withInput()->withErrors([
                    'versi' => 'Siswa baru saja mengirim jawaban baru. Halaman dimuat ulang, silakan periksa lagi sebelum menilai.',
                ]);
            }
        }

        $rules = [];
        foreach ($gradable as $item) {
            $id = $item['case']->id;
            foreach (self::ASPECTS as $aspect) {
                $rules["nilai.$id.$aspect"] = ['required', 'integer', 'between:0,100'];
            }
            $rules["komentar.$id"] = ['nullable', 'string', 'max:2000'];
        }

        $data = $request->validate($rules, [
            'required' => 'Nilai wajib diisi.',
            'integer'  => 'Nilai harus berupa angka bulat.',
            'between'  => 'Nilai harus antara 0 sampai 100.',
            'max'      => 'Komentar maksimal 2000 karakter.',
        ]);

        DB::transaction(function () use ($gradable, $data, $student) {
            foreach ($gradable as $item) {
                $id = $item['case']->id;
                $score = $data['nilai'][$id];

                TeacherAssessment::updateOrCreate(
                    ['case_id' => $id, 'user_id' => $student->id],
                    [
                        'submission_id'      => $item['latest']->id, // jawaban terakhir ditentukan server
                        'nilai_identifikasi' => (int) $score['identifikasi'],
                        'nilai_analisis'     => (int) $score['analisis'],
                        'nilai_solusi'       => (int) $score['solusi'],
                        'final_score'        => GradeCalculator::finalScore(
                            (int) $score['identifikasi'], (int) $score['analisis'], (int) $score['solusi']
                        ),
                        'comment'            => $data['komentar'][$id] ?? null,
                        'graded_at'          => now(),
                    ]
                );
            }
        });

        return redirect()->route('guru.submissions.show', [$room, $student])
            ->with('success', 'Nilai kuis berhasil disimpan.');
    }
}