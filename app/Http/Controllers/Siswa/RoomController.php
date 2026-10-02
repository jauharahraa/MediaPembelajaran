<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = $request->user()->joinedRooms()->withCount('cases')->with('teacher')->get();

        return view('siswa.rooms.index', compact('rooms'));
    }

    public function show(Request $request, Room $room)
    {
        Gate::authorize('access', $room);

        $room->load(['materials', 'videos', 'cases', 'teacher']);

        // Status tiap kasus dihitung dari data untuk siswa yang sedang login
        $statuses = $room->cases->mapWithKeys(fn ($case) => [$case->id => $case->statusFor($request->user()->id)]);

        return view('siswa.rooms.show', compact('room', 'statuses'));
    }
}