<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    // Guru pemilik room
    public function manage(User $user, Room $room): bool
    {
        return $user->isGuru() && $room->teacher_id === $user->id;
    }

    // Siswa yang sudah bergabung dan room aktif
    public function access(User $user, Room $room): bool
    {
        return $user->isSiswa() && $room->is_active && $room->hasMember($user);
    }

    // Siswa anggota room boleh melihat hasil walaupun room nonaktif
    public function view(User $user, Room $room): bool
    {
        return $user->isSiswa() && $room->hasMember($user);
    }
}