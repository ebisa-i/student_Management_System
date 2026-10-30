<?php

use App\Http\Controllers\AdminResultController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::get('/register', [AuthController::class, 'showRegister'])->middleware('guest')->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware(['guest', 'throttle:6,1'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/courses', [CourseController::class, 'adminIndex'])->name('courses.index');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::put('/courses/{course}/academic-term', [CourseController::class, 'updateAcademicTerm'])->name('courses.academic-term.update');
        Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::get('/results', [AdminResultController::class, 'index'])->name('results.index');
        Route::post('/results/{result}/approve', [AdminResultController::class, 'approve'])->name('results.approve');
        Route::post('/results/{result}/return', [AdminResultController::class, 'returnForEdits'])->name('results.return');
    });

    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/courses', [CourseController::class, 'teacherIndex'])->name('courses.index');
        Route::get('/results', [ResultController::class, 'teacherIndex'])->name('results.index');
        Route::post('/results', [ResultController::class, 'store'])->name('results.store');
        Route::put('/results/{result}', [ResultController::class, 'update'])->name('results.update');
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/courses', [CourseController::class, 'studentIndex'])->name('courses.index');
        Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
        Route::get('/results/download', [ResultController::class, 'studentExport'])->name('results.export');
        Route::get('/results', [ResultController::class, 'studentIndex'])->name('results.index');
    });
});
