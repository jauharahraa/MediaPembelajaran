<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;
use App\Models\CaseSubmission;
use App\Models\Material;
use App\Models\TeacherAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        // Ringkasan data yang ikut terhapus, ditampilkan di jendela konfirmasi
        if ($user->isGuru()) {
            $roomIds = $user->rooms()->pluck('id');
            $caseIds = CaseStudy::whereIn('room_id', $roomIds)->pluck('id');

            $impact = [
                'Room'                 => $roomIds->count(),
                'Siswa yang tergabung' => DB::table('room_members')->whereIn('room_id', $roomIds)->distinct()->count('user_id'),
                'Kasus'                => $caseIds->count(),
                'Jawaban siswa'        => CaseSubmission::whereIn('case_id', $caseIds)->count(),
            ];
        } else {
            $impact = [
                'Room yang diikuti' => $user->joinedRooms()->count(),
                'Jawaban terkirim'  => $user->submissions()->count(),
                'Nilai dari guru'   => TeacherAssessment::where('user_id', $user->id)->count(),
            ];
        }

        return view('profile.edit', compact('user', 'impact'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'kelas' => [$user->isSiswa() ? 'required' : 'nullable', 'string', 'max:50'],
        ]);

        $user->update([
            'name'  => $data['name'],
            'kelas' => $user->isSiswa() ? $data['kelas'] : null,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function destroy(Request $request)
    {
        // Error disimpan di bag terpisah agar tidak tercampur dengan form profil
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ], [
            'password.required'         => 'Masukkan kata sandi untuk melanjutkan.',
            'password.current_password' => 'Kata sandi salah.',
        ]);

        $user = $request->user();

        // Gambar materi milik guru tidak ikut terhapus otomatis oleh database
        $imagePaths = $user->isGuru()
            ? Material::whereIn('room_id', $user->rooms()->pluck('id'))
                ->whereNotNull('image_path')->pluck('image_path')->all()
            : [];

        // Logout lebih dulu, baru hapus. Kebalikannya bisa membuat Laravel menyimpan ulang user yang sudah dihapus
        Auth::logout();

        DB::transaction(function () use ($user) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            $user->delete(); // room, keanggotaan, jawaban, feedback, dan nilai ikut terhapus lewat foreign key
        });

        Storage::disk('public')->delete($imagePaths);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing')->with('success', 'Akun Anda telah dihapus.');
    }
}