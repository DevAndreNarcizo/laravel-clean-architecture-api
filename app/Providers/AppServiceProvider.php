<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Domain\Project\ProjectRepository;
use Src\Domain\Project\TaskRepository;
use Src\Infrastructure\Persistence\Eloquent\Repositories\EloquentProjectRepository;
use Src\Infrastructure\Persistence\Eloquent\Repositories\EloquentTaskRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProjectRepository::class, EloquentProjectRepository::class);
        $this->app->bind(TaskRepository::class, EloquentTaskRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
