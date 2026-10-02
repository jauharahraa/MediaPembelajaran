<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\CaseSubmission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user();

        // Hanya jawaban terakhir tiap siswa per kasus, dari room milik guru ini
        $latestIds = CaseSubmission::query()
            ->whereHas('case.room', fn ($q) => $q->where('teacher_id', $teacher->id))
            ->selectRaw('MAX(case_submissions.id)')
            ->groupBy('case_id', 'user_id');

        $submissions = CaseSubmission::with(['student', 'case.room', 'assessment'])
            ->whereIn('id', $latestIds)
            ->when($request->room_id, fn ($q, $v) => $q->whereHas('case', fn ($c) => $c->where('room_id', $v)))
            ->when($request->case_id, fn ($q, $v) => $q->where('case_id', $v))
            ->when($request->status === 'belum', fn ($q) => $q->doesntHave('assessment'))
            ->when($request->status === 'sudah', fn ($q) => $q->has('assessment'))
            ->when($request->q, fn ($q, $v) => $q->whereHas('student', fn ($s) => $s->where('name', 'like', "%{$v}%")))
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        // attempt_number pada jawaban terakhir = jumlah percobaan siswa
        $rooms = $teacher->rooms()->with('cases')->get();

        return view('guru.submissions.index', compact('submissions', 'rooms'));
    }

    public function show(CaseStudy $case, User $student)
    {
        Gate::authorize('manage', $case);

        $submissions = $case->submissions()->where('user_id', $student->id)
            ->with('feedback')->orderBy('attempt_number')->get();
        abort_if($submissions->isEmpty(), 404);

        $case->load('rubric', 'room');
        $assessment = $case->assessments()->where('user_id', $student->id)->first();

        return view('guru.submissions.show', compact('case', 'student', 'submissions', 'assessment'));
    }
}