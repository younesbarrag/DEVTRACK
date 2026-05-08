<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskApiController;


// Get all tasks of a specific project
Route::get('/projects/{project}/tasks', [TaskApiController::class, 'index'])
    ->name('tasks.index');

// Create a new task inside a project
Route::post('/projects/{project}/tasks', [TaskApiController::class, 'store'])
    ->name('tasks.store');

// Update task status
Route::patch('/tasks/{task}/status', [TaskApiController::class, 'update'])
    ->name('tasks.update');

// Delete a task
Route::delete('/tasks/{task}', [TaskApiController::class, 'destroy'])
    ->name('tasks.destroy');










