<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(Auth::user()->role.'.dashboard');
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::get('/guru/dashboard', [DashboardController::class, 'guru'])
        ->middleware('role:guru')
        ->name('guru.dashboard');

    Route::post('/guru/subjects', [DashboardController::class, 'storeSubject'])
        ->middleware('role:guru')
        ->name('guru.subjects.store');

    Route::get('/subjects/{subject}', [DashboardController::class, 'showSubject'])
        ->whereNumber('subject')
        ->name('subjects.show');

    Route::post('/guru/subjects/{subject}/materials', [DashboardController::class, 'storeMaterial'])
        ->middleware('role:guru')
        ->whereNumber('subject')
        ->name('guru.subjects.materials.store');

    Route::get('/siswa/dashboard', [DashboardController::class, 'siswa'])
        ->middleware('role:siswa')
        ->name('siswa.dashboard');

    Route::get('/siswa/profile', [DashboardController::class, 'showSiswaProfile'])
        ->middleware('role:siswa')
        ->name('siswa.profile');

    Route::put('/siswa/profile', [DashboardController::class, 'updateSiswaProfile'])
        ->middleware('role:siswa')
        ->name('siswa.profile.update');
});
