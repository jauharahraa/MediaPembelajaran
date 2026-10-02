<?php

namespace App\Policies;

use App\Models\CaseSubmission;
use App\Models\User;

class CaseSubmissionPolicy
{
    // Guru hanya boleh menilai pengerjaan di room miliknya
    public function grade(User $user, CaseSubmission $submission): bool
    {
        return $user->isGuru() && $submission->case->room->teacher_id === $user->id;
    }

    // Siswa hanya boleh melihat jawabannya sendiri
    public function view(User $user, CaseSubmission $submission): bool
    {
        return $user->isSiswa() && $submission->user_id === $user->id;
    }
}