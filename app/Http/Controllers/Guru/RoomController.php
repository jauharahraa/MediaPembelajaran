<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = $request->user()->rooms()
            ->withCount(['members', 'cases', 'materials', 'videos'])
            ->latest()->get();

        return view('guru.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('guru.rooms.form', ['room' => new Room(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $room = $request->user()->rooms()->create(
            $this->validated($request) + ['code' => Room::generateCode()]
        );

        return redirect()->route('guru.rooms.show', $room)
            ->with('success', 'Room berhasil dibuat. Kode room: ' . $room->code);
    }

    public function show(Room $room)
    {
        Gate::authorize('manage', $room);
        $room->load(['materials', 'videos', 'cases.rubric'])->loadCount('members');

        return view('guru.rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        Gate::authorize('manage', $room);

        return view('guru.rooms.form', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        Gate::authorize('manage', $room);
        $room->update($this->validated($request));

        return redirect()->route('guru.rooms.show', $room)->with('success', 'Room berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        Gate::authorize('manage', $room);
        $room->delete();

        return redirect()->route('guru.rooms.index')->with('success', 'Room berhasil dihapus.');
    }

    public function toggle(Room $room)
    {
        Gate::authorize('manage', $room);
        $room->update(['is_active' => ! $room->is_active]);

        return back()->with('success', $room->is_active ? 'Room diaktifkan.' : 'Room dinonaktifkan.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'subject'     => ['required', 'string', 'max:100'],
            'topic'       => ['required', 'string', 'max:150'],
            'class_level' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}