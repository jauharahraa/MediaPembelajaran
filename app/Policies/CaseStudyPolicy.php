<?php

namespace App\Policies;

use App\Models\CaseStudy;
use App\Models\User;

class CaseStudyPolicy
{
    public function manage(User $user, CaseStudy $case): bool
    {
        return $user->isGuru() && $case->room->teacher_id === $user->id;
    }

    public function answer(User $user, CaseStudy $case): bool
    {
        return $user->isSiswa() && $case->room->is_active && $case->room->hasMember($user);
    }
}