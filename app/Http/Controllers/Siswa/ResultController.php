<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ResultController extends Controller
{
    public function index(Request $request, Room $room, QuizService $quizService)
    {
        Gate::authorize('view', $room);

        $quiz = $quizService->build($room, $request->user());

        return view('siswa.results.index', compact('room', 'quiz'));
    }
}