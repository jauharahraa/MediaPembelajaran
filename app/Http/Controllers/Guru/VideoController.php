<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VideoController extends Controller
{
    public function create(Room $room)
    {
        Gate::authorize('manage', $room);

        return view('guru.videos.form', ['room' => $room, 'video' => new Video]);
    }

    public function store(Request $request, Room $room)
    {
        Gate::authorize('manage', $room);
        $room->videos()->create($this->validated($request));

        return redirect()->route('guru.rooms.show', $room)->with('success', 'Video berhasil ditambahkan.');
    }

    public function edit(Room $room, Video $video)
    {
        $this->authorizeVideo($room, $video);

        return view('guru.videos.form', compact('room', 'video'));
    }

    public function update(Request $request, Room $room, Video $video)
    {
        $this->authorizeVideo($room, $video);
        $video->update($this->validated($request));

        return redirect()->route('guru.rooms.show', $room)->with('success', 'Video berhasil diperbarui.');
    }

    public function destroy(Room $room, Video $video)
    {
        $this->authorizeVideo($room, $video);
        $video->delete();

        return redirect()->route('guru.rooms.show', $room)->with('success', 'Video berhasil dihapus.');
    }

    private function authorizeVideo(Room $room, Video $video): void
    {
        Gate::authorize('manage', $room);
        abort_unless($video->room_id === $room->id, 404);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:150'],
            'youtube_url' => ['required', 'url', 'max:255'],
        ]);

        $youtubeId = Video::extractYoutubeId($data['youtube_url']);

        if (! $youtubeId) {
            abort(back()->withErrors(['youtube_url' => 'Tautan YouTube tidak valid.'])->withInput());
        }

        return $data + ['youtube_id' => $youtubeId];
    }
}