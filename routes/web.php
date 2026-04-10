<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\TasksController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store']);

Route::post('/logout', [LogoutController::class, 'store'])->name('logout');

Route::middleware('auth.session')->group(function (): void {
    Route::get('/projects', [ProjectsController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectsController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectsController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectsController::class, 'show'])
        ->whereNumber('project')
        ->name('projects.show');

    Route::get('/projects/{project}/tasks/create', [TasksController::class, 'create'])
        ->whereNumber('project')
        ->name('tasks.create');
    Route::post('/projects/{project}/tasks', [TasksController::class, 'store'])
        ->whereNumber('project')
        ->name('tasks.store');
});
