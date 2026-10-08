<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\User;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SubmissionController extends Controller
{
    // Satu baris = satu siswa pada satu room (satu kuis)
    public function index(Request $request)
    {
        $teacher = $request->user();

        $rows = DB::table('case_submissions as cs')
            ->join('cases as c', 'c.id', '=', 'cs.case_id')
            ->join('rooms as r', 'r.id', '=', 'c.room_id')
            ->join('users as u', 'u.id', '=', 'cs.user_id')
            ->where('r.teacher_id', $teacher->id) // hanya room milik guru ini
            ->when($request->room_id, fn ($q, $v) => $q->where('r.id', $v))
            ->when($request->q, fn ($q, $v) => $q->where('u.name', 'like', '%' . $v . '%'))
            ->groupBy('r.id', 'r.name', 'u.id', 'u.name', 'u.kelas')
            ->selectRaw('
                r.id as room_id, r.name as room_name,
                u.id as student_id, u.name as student_name, u.kelas,
                COUNT(DISTINCT cs.case_id) as answered,
                MAX(cs.attempt_number) as attempts,
                MAX(cs.submitted_at) as last_submitted_at,
                (SELECT COUNT(*) FROM cases c2 WHERE c2.room_id = r.id) as total_cases,
                (SELECT COUNT(*) FROM teacher_assessments ta JOIN cases c3 ON c3.id = ta.case_id
                    WHERE c3.room_id = r.id AND ta.user_id = u.id) as graded,
                (SELECT AVG(ta2.final_score) FROM teacher_assessments ta2 JOIN cases c4 ON c4.id = ta2.case_id
                    WHERE c4.room_id = r.id AND ta2.user_id = u.id) as avg_score
            ')
            ->when($request->status === 'sudah', fn ($q) => $q->havingRaw('graded >= total_cases'))
            ->when($request->status === 'belum', fn ($q) => $q->havingRaw('graded < total_cases'))
            ->orderByDesc('last_submitted_at')
            ->paginate(15)
            ->withQueryString();

        $rows->through(function ($row) {
            $row->complete = $row->total_cases > 0 && $row->graded >= $row->total_cases;
            $row->last_submitted_at = Carbon::parse($row->last_submitted_at);

            return $row;
        });

        $rooms = $teacher->rooms()->orderBy('name')->get();

        return view('guru.submissions.index', compact('rows', 'rooms'));
    }

    public function show(Room $room, User $student, QuizService $quizService)
    {
        Gate::authorize('manage', $room); // guru hanya boleh memeriksa room miliknya
        abort_unless($student->isSiswa() && $room->hasMember($student), 404);

        $quiz = $quizService->build($room, $student);
        abort_if($quiz['answered'] === 0, 404);

        $awaitingRevision = $quiz['items']->contains(
            fn ($i) => in_array($i['mode'], ['revisi', 'menunggu_feedback'], true)
        );

        return view('guru.submissions.show', compact('room', 'student', 'quiz', 'awaitingRevision'));
    }
}