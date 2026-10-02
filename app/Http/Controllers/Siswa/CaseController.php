<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CaseController extends Controller
{
    public function show(Request $request, Room $room, CaseStudy $case)
    {
        Gate::authorize('access', $room);
        abort_unless($case->room_id === $room->id, 404);

        $userId = $request->user()->id;

        $submissions = $case->submissions()->where('user_id', $userId)
            ->with('feedback')->orderBy('attempt_number')->get();
        $assessment = $case->assessments()->where('user_id', $userId)->first();
        $latest = $submissions->last();

        // Menentukan form apa yang ditampilkan di view
        $mode = match (true) {
            (bool) $assessment                                                   => 'dinilai',
            $submissions->count() === 0                                          => 'pertama',
            $submissions->count() === 1 && $latest->feedback?->status === 'success' => 'revisi',
            $submissions->count() === 1                                          => 'menunggu_feedback',
            default                                                              => 'selesai',
        };

        return view('siswa.cases.show', compact('room', 'case', 'submissions', 'assessment', 'mode'));
    }
}