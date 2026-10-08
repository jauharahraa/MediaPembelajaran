<?php

namespace App\Services;

use App\Models\CaseSubmission;
use App\Models\Room;
use App\Models\TeacherAssessment;
use App\Models\User;

class QuizService
{
    public function build(Room $room, User $student): array
    {
        $cases = $room->cases()->with('rubric')->get()->values();
        $caseIds = $cases->pluck('id');

        $submissions = CaseSubmission::with('feedback')
            ->where('user_id', $student->id)
            ->whereIn('case_id', $caseIds)
            ->orderBy('attempt_number')
            ->get()
            ->groupBy('case_id');

        $assessments = TeacherAssessment::where('user_id', $student->id)
            ->whereIn('case_id', $caseIds)
            ->get()
            ->keyBy('case_id');

        $items = $cases->map(function ($case, $index) use ($submissions, $assessments) {
            $subs = $submissions->get($case->id, collect());
            $assessment = $assessments->get($case->id);
            $latest = $subs->last();

            $mode = match (true) {
                (bool) $assessment                                                       => 'dinilai',
                $subs->count() === 0                                                     => 'pertama',
                $subs->count() === 1 && $latest->feedback?->status === 'success'         => 'revisi',
                $subs->count() === 1                                                     => 'menunggu_feedback',
                default                                                                  => 'selesai',
            };

            return [
                'number'     => $index + 1,
                'case'       => $case,
                'first'      => $subs->firstWhere('attempt_number', 1),
                'second'     => $subs->firstWhere('attempt_number', 2),
                'latest'     => $latest,
                'assessment' => $assessment,
                'mode'       => $mode,
                'editable'   => in_array($mode, ['pertama', 'revisi'], true),
            ];
        });

        $total = $items->count();
        $answered = $items->filter(fn ($i) => $i['latest'] !== null)->count();
        $graded = $items->filter(fn ($i) => $i['assessment'] !== null)->count();
        $complete = $total > 0 && $graded === $total;

        // Nilai akhir kuis hanya muncul jika SEMUA soal sudah dinilai guru
        $finalScore = $complete
            ? round($items->avg(fn ($i) => (float) $i['assessment']->final_score), 2)
            : null;

        $status = match (true) {
            $complete                                                                          => 'Sudah Dinilai',
            $answered === 0                                                                    => 'Belum Dikerjakan',
            $items->contains(fn ($i) => $i['mode'] === 'pertama')                              => 'Belum Lengkap',
            $items->contains(fn ($i) => in_array($i['mode'], ['revisi', 'menunggu_feedback'], true)) => 'Menunggu Revisi',
            default                                                                            => 'Menunggu Penilaian Guru',
        };

        return [
            'items'       => $items,
            'total'       => $total,
            'answered'    => $answered,
            'graded'      => $graded,
            'complete'    => $complete,
            'finalScore'  => $finalScore,
            'hasEditable' => $items->contains(fn ($i) => $i['editable']),
            'status'      => $status,
        ];
    }
}