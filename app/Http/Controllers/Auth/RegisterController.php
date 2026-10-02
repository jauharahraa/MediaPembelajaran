<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'    => ['required', 'confirmed', Password::min(8)],
            'role'        => ['required', 'in:guru,siswa'],
            'kelas'       => ['required_if:role,siswa', 'nullable', 'string', 'max:50'],
            'invite_code' => ['required_if:role,guru', 'nullable', 'string'],
        ]);

        // Guru wajib memakai kode undangan karena tidak ada peran Admin
        if ($data['role'] === 'guru'
            && ! hash_equals((string) config('casemethod.teacher_invite_code'), (string) $data['invite_code'])) {
            return back()->withErrors(['invite_code' => 'Kode undangan guru tidak valid.'])->withInput();
        }

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'], // otomatis di-hash oleh cast 'hashed'
            'role'     => $data['role'],
            'kelas'    => $data['role'] === 'siswa' ? $data['kelas'] : null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($user->isGuru() ? 'guru.dashboard' : 'siswa.dashboard')
            ->with('success', 'Akun berhasil dibuat. Selamat datang!');
    }
}