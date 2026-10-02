<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ResultController extends Controller
{
    public function index(Request $request, Room $room)
    {
        Gate::authorize('view', $room);

        $userId = $request->user()->id;
        $cases = $room->cases()->get()->map(function ($case) use ($userId) {
            $case->status = $case->statusFor($userId);
            $case->assessment = $case->assessments()->where('user_id', $userId)->first();

            return $case;
        });

        return view('siswa.results.index', compact('room', 'cases'));
    }

    public function show(Request $request, Room $room, CaseStudy $case)
    {
        Gate::authorize('view', $room);
        abort_unless($case->room_id === $room->id, 404);

        $userId = $request->user()->id;
        $submissions = $case->submissions()->where('user_id', $userId)->with('feedback')->orderBy('attempt_number')->get();
        abort_if($submissions->isEmpty(), 404);

        $latest = $submissions->last();
        // Nilai hanya tampil jika guru sudah menyimpan penilaian
        $assessment = $case->assessments()->where('user_id', $userId)->first();

        return view('siswa.results.show', compact('room', 'case', 'latest', 'assessment'));
    }
}