<?php

namespace App\Http\Controllers\Siswa;

use App\Exceptions\SubmissionException;
use App\Http\Controllers\Controller;
use App\Models\CaseSubmission;
use App\Models\Room;
use App\Services\GeminiService;
use App\Services\QuizService;
use App\Services\SubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class QuizController extends Controller
{
    public function show(Request $request, Room $room, QuizService $quizService)
    {
        Gate::authorize('view', $room);

        $quiz = $quizService->build($room, $request->user());
        $locked = ! $room->is_active;

        return view('siswa.quiz.show', compact('room', 'quiz', 'locked'));
    }

    public function store(Request $request, Room $room, SubmissionService $service, GeminiService $gemini)
    {
        Gate::authorize('access', $room); // anggota room dan room aktif

        $user = $request->user();
        $cases = $room->cases()->get()->values();

        $request->validate([
            'answers'                        => ['required', 'array', 'min:1'],
            'answers.*.jawaban_identifikasi' => ['required', 'string', 'min:10', 'max:5000'],
            'answers.*.jawaban_analisis'     => ['required', 'string', 'min:10', 'max:5000'],
            'answers.*.jawaban_solusi'       => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'required' => 'Jawaban wajib diisi.',
            'min'      => 'Jawaban minimal 10 karakter.',
            'max'      => 'Jawaban maksimal 5000 karakter.',
        ]);

        $answers = $request->input('answers');
        $sentIds = array_map('intval', array_keys($answers));

        // Soal yang dikirim harus milik room ini
        abort_unless(empty(array_diff($sentIds, $cases->pluck('id')->all())), 422, 'Soal tidak valid.');

        // Setiap soal yang belum pernah dijawab wajib ikut dikirim
        $alreadyAnswered = CaseSubmission::where('user_id', $user->id)
            ->whereIn('case_id', $cases->pluck('id'))->pluck('case_id')->all();

        $missing = $cases->filter(fn ($c) => ! in_array($c->id, $alreadyAnswered) && ! in_array($c->id, $sentIds));
        if ($missing->isNotEmpty()) {
            return back()->withErrors(['jawaban' => 'Semua soal harus dijawab sebelum dikirim.'])->withInput();
        }

        // Semua soal disimpan atau tidak sama sekali. Jatah revisi dijaga SubmissionService per soal
        $created = [];

        try {
            DB::transaction(function () use ($cases, $answers, $service, $user, &$created) {
                foreach ($cases as $index => $case) {
                    if (! isset($answers[$case->id])) {
                        continue;
                    }

                    try {
                        $created[] = $service->submit($case, $user, Arr::only($answers[$case->id], [
                            'jawaban_identifikasi', 'jawaban_analisis', 'jawaban_solusi',
                        ]));
                    } catch (SubmissionException $e) {
                        throw new SubmissionException('Soal ' . ($index + 1) . ': ' . $e->getMessage());
                    }
                }
            });
        } catch (SubmissionException $e) {
            return back()->withErrors(['jawaban' => $e->getMessage()])->withInput();
        }

        // Gemini dipanggil SETELAH semua jawaban tersimpan. Kegagalan tidak menghapus jawaban.
        foreach ($created as $submission) {
            try {
                $gemini->generateFeedback($submission);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('siswa.rooms.quiz', $room)->with('success', 'Jawaban berhasil dikirim.');
    }
}