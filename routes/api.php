<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Interface\Http\Controllers\ProjectController;
use Src\Interface\Http\Controllers\TaskController;

Route::prefix('v1')->group(function (): void {
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store']);
});
