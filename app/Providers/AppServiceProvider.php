<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Src\Application\Auth\JwtTokenService;
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
        $this->app->singleton(JwtTokenService::class, static fn (): JwtTokenService => new JwtTokenService((string) config('app.key')));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', static fn (Request $request): Limit => Limit::perMinute(120)->by((string) ($request->user()->id ?? $request->ip())));
        RateLimiter::for('login', static fn (Request $request): Limit => Limit::perMinute(5)->by((string) $request->ip()));
    }
}
