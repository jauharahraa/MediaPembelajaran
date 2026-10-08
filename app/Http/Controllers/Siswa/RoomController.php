<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = $request->user()->joinedRooms()->withCount('cases')->with('teacher')->get();

        return view('siswa.rooms.index', compact('rooms'));
    }

    public function show(Request $request, Room $room, QuizService $quizService)
    {
        Gate::authorize('view', $room);

        $room->load(['materials', 'videos', 'cases', 'teacher']);

        // Room nonaktif: hanya tab Informasi dan Hasil
        $locked = ! $room->is_active;

        $tabs = ['info' => ['Informasi Room', 'bi-info-circle']];
        if (! $locked) {
            $tabs += [
                'materi' => ['Materi', 'bi-journal-text'],
                'video'  => ['Video', 'bi-play-circle'],
                'kuis'   => ['Kuis Case Method', 'bi-puzzle'],
            ];
        }
        $tabs['hasil'] = ['Hasil Pengerjaan', 'bi-patch-check'];

        $quiz = $quizService->build($room, $request->user());

        return view('siswa.rooms.show', compact('room', 'locked', 'tabs', 'quiz'));
    }
}