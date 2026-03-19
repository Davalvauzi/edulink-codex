<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialSubsectionController;
use App\Http\Controllers\SubjectController;
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

    Route::get('/guru/subjects/create', [SubjectController::class, 'create'])
        ->middleware('role:guru')
        ->name('guru.subjects.create');

    Route::post('/guru/subjects', [SubjectController::class, 'store'])
        ->middleware('role:guru')
        ->name('guru.subjects.store');

    Route::get('/subjects/{subject}', [SubjectController::class, 'show'])
        ->whereNumber('subject')
        ->name('subjects.show');

    Route::get('/subjects/{subject}/materials/create', [MaterialController::class, 'create'])
        ->middleware('role:guru')
        ->whereNumber('subject')
        ->name('guru.subjects.materials.create');

    Route::post('/subjects/{subject}/materials', [MaterialController::class, 'store'])
        ->middleware('role:guru')
        ->whereNumber('subject')
        ->name('guru.subjects.materials.store');

    Route::get('/subjects/{subject}/materials/{material}', [MaterialController::class, 'show'])
        ->whereNumber('subject')
        ->whereNumber('material')
        ->name('materials.show');

    Route::get('/subjects/{subject}/materials/{material}/subsections/create', [MaterialSubsectionController::class, 'create'])
        ->middleware('role:guru')
        ->whereNumber('subject')
        ->whereNumber('material')
        ->name('guru.materials.subsections.create');

    Route::post('/subjects/{subject}/materials/{material}/subsections', [MaterialSubsectionController::class, 'store'])
        ->middleware('role:guru')
        ->whereNumber('subject')
        ->whereNumber('material')
        ->name('guru.materials.subsections.store');

    Route::get('/subjects/{subject}/materials/{material}/subsections/{subsection}', [MaterialSubsectionController::class, 'show'])
        ->whereNumber('subject')
        ->whereNumber('material')
        ->whereNumber('subsection')
        ->name('materials.subsections.show');

    Route::get('/subjects/{subject}/materials/{material}/subsections/{subsection}/edit', [MaterialSubsectionController::class, 'edit'])
        ->middleware('role:guru')
        ->whereNumber('subject')
        ->whereNumber('material')
        ->whereNumber('subsection')
        ->name('guru.materials.subsections.edit');

    Route::put('/subjects/{subject}/materials/{material}/subsections/{subsection}', [MaterialSubsectionController::class, 'update'])
        ->middleware('role:guru')
        ->whereNumber('subject')
        ->whereNumber('material')
        ->whereNumber('subsection')
        ->name('guru.materials.subsections.update');

    Route::delete('/subjects/{subject}/materials/{material}/subsections/{subsection}', [MaterialSubsectionController::class, 'destroy'])
        ->middleware('role:guru')
        ->whereNumber('subject')
        ->whereNumber('material')
        ->whereNumber('subsection')
        ->name('guru.materials.subsections.destroy');

    Route::get('/subjects/{subject}/materials/{material}/edit', [MaterialController::class, 'edit'])
        ->middleware('role:guru')
        ->whereNumber('subject')
        ->whereNumber('material')
        ->name('guru.materials.edit');

    Route::put('/subjects/{subject}/materials/{material}', [MaterialController::class, 'update'])
        ->middleware('role:guru')
        ->whereNumber('subject')
        ->whereNumber('material')
        ->name('guru.materials.update');

    Route::delete('/subjects/{subject}/materials/{material}', [MaterialController::class, 'destroy'])
        ->middleware('role:guru')
        ->whereNumber('subject')
        ->whereNumber('material')
        ->name('guru.materials.destroy');

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
