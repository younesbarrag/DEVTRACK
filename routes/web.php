<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('register');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/archives/projects', [ProjectController::class, 'showArchives'])->name('projects.archives');
    Route::patch('/archives/projects/{id}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
    Route::resource('projects', ProjectController::class);
    Route::resource('projects.tasks', TaskController::class);
});

require __DIR__.'/auth.php';