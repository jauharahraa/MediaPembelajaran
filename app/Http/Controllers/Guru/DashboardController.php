<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\CaseSubmission;
use App\Models\TeacherAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $roomIds = $request->user()->rooms()->pluck('id');
        $caseIds = CaseStudy::whereIn('room_id', $roomIds)->pluck('id');

        // Setiap siswa punya tepat satu baris attempt 1 per kasus
        $totalPengerjaan = CaseSubmission::whereIn('case_id', $caseIds)->where('attempt_number', 1)->count();
        $sudahDinilai = TeacherAssessment::whereIn('case_id', $caseIds)->count();

        $stats = [
            'rooms'       => $roomIds->count(),
            'students'    => DB::table('room_members')->whereIn('room_id', $roomIds)->distinct()->count('user_id'),
            'cases'       => $caseIds->count(),
            'submissions' => $totalPengerjaan,
            'ungraded'    => $totalPengerjaan - $sudahDinilai,
        ];

        $recent = CaseSubmission::with(['student', 'case.room'])
            ->whereIn('case_id', $caseIds)->latest('submitted_at')->limit(8)->get();

        return view('guru.dashboard', compact('stats', 'recent'));
    }
}