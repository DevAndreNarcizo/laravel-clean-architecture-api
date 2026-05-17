<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Interface\Http\Controllers\AuthController;
use Src\Interface\Http\Controllers\ProjectController;
use Src\Interface\Http\Controllers\TaskController;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/auth/refresh', [AuthController::class, 'refresh'])->middleware('throttle:login');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('throttle:api');
    Route::get('/auth/me', [AuthController::class, 'me'])->middleware(['jwt', 'throttle:api']);

    Route::middleware(['jwt', 'throttle:api'])->group(function (): void {
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
    });
});
