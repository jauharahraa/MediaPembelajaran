<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\CaseSubmission;
use App\Models\TeacherAssessment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $rooms = $user->joinedRooms()->withCount('cases')->with('teacher')->get();
        $caseIds = CaseStudy::whereIn('room_id', $rooms->pluck('id'))->pluck('id');

        $answered = CaseSubmission::where('user_id', $user->id)->whereIn('case_id', $caseIds)->distinct()->count('case_id');

        $stats = [
            'answered' => $answered,
            'pending'  => $caseIds->count() - $answered,
            'graded'   => TeacherAssessment::where('user_id', $user->id)->whereIn('case_id', $caseIds)->count(),
        ];

        $recent = CaseSubmission::with('case.room')->where('user_id', $user->id)
            ->latest('submitted_at')->limit(5)->get();

        return view('siswa.dashboard', compact('user', 'rooms', 'stats', 'recent'));
    }
}