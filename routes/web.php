<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Guru\AssessmentController;
use App\Http\Controllers\Guru\CaseController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\MaterialController;
use App\Http\Controllers\Guru\RoomController;
use App\Http\Controllers\Guru\SubmissionController as GuruSubmissionController;
use App\Http\Controllers\Guru\VideoController;
use App\Http\Controllers\Siswa\CaseController as SiswaCaseController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Siswa\JoinRoomController;
use App\Http\Controllers\Siswa\ResultController;
use App\Http\Controllers\Siswa\RoomController as SiswaRoomController;
use App\Http\Controllers\Siswa\SubmissionController as SiswaSubmissionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
    Route::patch('rooms/{room}/toggle', [RoomController::class, 'toggle'])->name('rooms.toggle');
    Route::resource('rooms', RoomController::class);
    Route::resource('rooms.materials', MaterialController::class)->except(['index', 'show']);
    Route::resource('rooms.videos', VideoController::class)->except(['index', 'show']);
    Route::post('rooms/{room}/cases/reorder', [CaseController::class, 'reorder'])->name('rooms.cases.reorder');
    Route::resource('rooms.cases', CaseController::class)->except(['index'])->parameters(['cases' => 'case']);
    
    // Rute tambahan penilaian guru
    Route::get('submissions', [GuruSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('submissions/{case}/{student}', [GuruSubmissionController::class, 'show'])->name('submissions.show');
    Route::post('submissions/{case}/{student}/assess', [AssessmentController::class, 'store'])->name('submissions.assess');
});

Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
    
    Route::get('join', [JoinRoomController::class, 'create'])->name('join');
    Route::post('join', [JoinRoomController::class, 'store'])->name('join.store');
    
    Route::get('rooms', [SiswaRoomController::class, 'index'])->name('rooms.index');
    Route::get('rooms/{room}', [SiswaRoomController::class, 'show'])->name('rooms.show');
    Route::get('rooms/{room}/cases/{case}', [SiswaCaseController::class, 'show'])->name('rooms.cases.show');
    
    Route::post('cases/{case}/submit', [SiswaSubmissionController::class, 'store'])->name('cases.submit');
    Route::post('submissions/{submission}/retry-feedback', [SiswaSubmissionController::class, 'retryFeedback'])->name('submissions.retry');
    
    Route::get('rooms/{room}/results', [ResultController::class, 'index'])->name('rooms.results.index');
    Route::get('rooms/{room}/results/{case}', [ResultController::class, 'show'])->name('rooms.results.show');
});