<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Stevebauman\Purify\Facades\Purify;

class MaterialController extends Controller
{
    public function create(Room $room)
    {
        Gate::authorize('manage', $room);

        return view('guru.materials.form', ['room' => $room, 'material' => new Material]);
    }

    public function store(Request $request, Room $room)
    {
        Gate::authorize('manage', $room);
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('materials', 'public');
        }

        $room->materials()->create($data);

        return redirect()->route('guru.rooms.show', $room)->with('success', 'Materi berhasil ditambahkan.');
    }

    public function edit(Room $room, Material $material)
    {
        $this->authorizeMaterial($room, $material);

        return view('guru.materials.form', compact('room', 'material'));
    }

    public function update(Request $request, Room $room, Material $material)
    {
        $this->authorizeMaterial($room, $material);
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($material->image_path) {
                Storage::disk('public')->delete($material->image_path);
            }
            $data['image_path'] = $request->file('image')->store('materials', 'public');
        }

        $material->update($data);

        return redirect()->route('guru.rooms.show', $room)->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Room $room, Material $material)
    {
        $this->authorizeMaterial($room, $material);

        if ($material->image_path) {
            Storage::disk('public')->delete($material->image_path);
        }
        $material->delete();

        return redirect()->route('guru.rooms.show', $room)->with('success', 'Materi berhasil dihapus.');
    }

    private function authorizeMaterial(Room $room, Material $material): void
    {
        Gate::authorize('manage', $room);
        abort_unless($material->room_id === $room->id, 404); // materi harus milik room di URL
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title'   => ['required', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'image'   => ['nullable', 'image', 'max:2048'],
        ]);

        $data['content'] = Purify::clean($data['content']); // buang script berbahaya
        unset($data['image']);

        return $data;
    }
}