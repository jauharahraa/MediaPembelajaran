<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class JoinRoomController extends Controller
{
    public function create()
    {
        return view('siswa.join');
    }

    public function store(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'max:12']]);

        $room = Room::where('code', strtoupper(trim($request->code)))->first();

        if (! $room || ! $room->is_active) {
            return back()->withErrors(['code' => 'Kode room tidak valid atau room tidak aktif.'])->withInput();
        }

        if ($room->hasMember($request->user())) {
            return redirect()->route('siswa.rooms.show', $room)->with('success', 'Anda sudah tergabung di room ini.');
        }

        $room->members()->attach($request->user()->id, ['joined_at' => now()]);

        return redirect()->route('siswa.rooms.show', $room)->with('success', 'Berhasil bergabung ke room ' . $room->name . '.');
    }
}