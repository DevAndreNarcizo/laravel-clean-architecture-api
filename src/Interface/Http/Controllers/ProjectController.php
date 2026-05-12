<?php

declare(strict_types=1);

namespace Src\Interface\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Src\Application\Project\CreateProjectData;
use Src\Application\Project\CreateProjectUseCase;
use Src\Infrastructure\Persistence\Eloquent\Models\ProjectModel;
use Src\Interface\Http\Requests\StoreProjectRequest;
use Src\Interface\Http\Resources\ApiResponse;

final readonly class ProjectController
{
    public function __construct(private CreateProjectUseCase $createProject) {}

    public function index(): JsonResponse
    {
        $ownerId = (int) request()->user()->id;
        $cacheKey = 'projects.owner.'.$ownerId.'.'.md5(json_encode(request()->query(), JSON_THROW_ON_ERROR));

        $projects = Cache::remember($cacheKey, now()->addMinutes(5), static fn () => ProjectModel::query()
            ->where('owner_id', $ownerId)
            ->when(request('search'), static fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->when(request('status'), static fn ($query, $status) => $query->where('status', $status))
            ->orderBy((string) request('sort_by', 'created_at'), (string) request('sort_dir', 'desc'))
            ->paginate((int) request('per_page', 15)));

        return ApiResponse::success($projects->items(), 200, [
            'current_page' => $projects->currentPage(),
            'per_page' => $projects->perPage(),
            'total' => $projects->total(),
            'last_page' => $projects->lastPage(),
        ]);
    }

    /**
     * Cria um projeto e retorna envelope JSON padronizado.
     *
     * @author André Narcizo
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->createProject->execute(new CreateProjectData(
            ownerId: (int) $request->user()->id,
            name: (string) $request->validated('name'),
            description: $request->validated('description'),
        ));

        Cache::flush();

        return ApiResponse::success($project, 201);
    }
}
