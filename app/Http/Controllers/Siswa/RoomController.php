<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\CaseSubmission;
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
        Gate::authorize('view', $room);

        $room->load(['materials', 'videos', 'cases', 'teacher']);
        $userId = $request->user()->id;

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

        // Status feedback AI pada jawaban terakhir tiap kasus
        $feedbackStatuses = CaseSubmission::with('feedback')
            ->where('user_id', $userId)
            ->whereIn('case_id', $room->cases->pluck('id'))
            ->orderBy('attempt_number')
            ->get()
            ->groupBy('case_id')
            ->map(fn ($group) => $group->last()->feedback?->status);

        $feedbackBadge = [
            'success' => ['Tersedia', 'text-bg-success'],
            'pending' => ['Diproses', 'text-bg-warning'],
            'failed'  => ['Gagal', 'text-bg-danger'],
        ];

        // Satu baris data siap pakai untuk tiap kasus, jadi view tidak perlu @php
        $caseRows = $room->cases->map(function ($case) use ($userId, $feedbackStatuses, $feedbackBadge) {
            $status = $case->statusFor($userId);
            [$fbLabel, $fbClass] = $feedbackBadge[$feedbackStatuses[$case->id] ?? ''] ?? ['Belum ada', 'text-bg-secondary'];

            return [
                'case'    => $case,
                'status'  => $status,
                'fbLabel' => $fbLabel,
                'fbClass' => $fbClass,
                'graded'  => $status === 'Sudah Dinilai',
            ];
        });

        return view('siswa.rooms.show', compact('room', 'locked', 'tabs', 'caseRows'));
    }
}