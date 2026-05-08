<?php

use App\Http\Controllers\Api\ProjectApiController;
use App\Http\Controllers\Api\TaskApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('projects', ProjectApiController::class);
    Route::apiResource('projects.tasks', TaskApiController::class);
});