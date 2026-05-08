<?php

use App\Http\Controllers\Api\ProjectApiController;
use App\Http\Controllers\Api\TaskApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('projects', ProjectApiController::class)->names([
        'index' => 'api.projects.index',
        'store' => 'api.projects.store',
        'show' => 'api.projects.show',
        'update' => 'api.projects.update',
        'destroy' => 'api.projects.destroy',
    ]);
    
    Route::apiResource('projects.tasks', TaskApiController::class)->names([
        'index' => 'api.projects.tasks.index',
        'store' => 'api.projects.tasks.store',
        'show' => 'api.projects.tasks.show',
        'update' => 'api.projects.tasks.update',
        'destroy' => 'api.projects.tasks.destroy',
    ]);
});