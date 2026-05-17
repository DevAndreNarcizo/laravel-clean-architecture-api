<?php

declare(strict_types=1);

namespace Src\Interface\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Src\Application\Project\CreateProjectData;
use Src\Application\Project\CreateProjectUseCase;
use Src\Domain\Project\Project;
use Src\Domain\Project\ProjectRepository;
use Src\Interface\Http\Requests\StoreProjectRequest;
use Src\Interface\Http\Requests\UpdateProjectRequest;
use Src\Interface\Http\Resources\ApiResponse;

final readonly class ProjectController
{
    public function __construct(
        private CreateProjectUseCase $createProject,
        private ProjectRepository $projectRepository,
    ) {}

    public function index(): JsonResponse
    {
        $result = $this->getCachedProjects();

        return ApiResponse::success($result['projects'], 200, $result['pagination']);
    }

    /**
     * Retorna lista de projetos do usuário autenticado, utilizando cache.
     *
     * @return array{projects: Project[], pagination: array{current_page: int, per_page: int, total: int, last_page: int}}
     */
    private function getCachedProjects(): array
    {
        $ownerId = (int) request()->user()->id;

        $params = array_filter(request()->only(['search', 'status', 'sort_by', 'sort_dir', 'per_page']));
        $cacheKey = 'projects:' . $ownerId . ':list:' . md5(json_encode($params));

        $allowedSortFields = ['id', 'name', 'created_at', 'updated_at'];
        $sortBy = in_array((string) request('sort_by', 'created_at'), $allowedSortFields, true)
            ? (string) request('sort_by', 'created_at')
            : 'created_at';
        $sortDir = in_array(strtolower((string) request('sort_dir', 'desc')), ['asc', 'desc'], true)
            ? strtolower((string) request('sort_dir', 'desc'))
            : 'desc';
        $perPage = max(1, min(100, (int) request('per_page', 15)));
        $search = request('search');
        $status = request('status');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($ownerId, $search, $status, $sortBy, $sortDir, $perPage): array {
            return $this->projectRepository->findByOwner(
                ownerId: $ownerId,
                search: $search !== null ? (string) $search : null,
                status: $status !== null ? (string) $status : null,
                sortBy: $sortBy,
                sortDir: $sortDir,
                perPage: $perPage,
            );
        });
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

        Cache::forget('projects:' . $request->user()->id . ':list');

        return ApiResponse::success($project, 201);
    }

    public function show(int $project): JsonResponse
    {
        $projectDomain = $this->projectRepository->findById($project);

        if ($projectDomain === null) {
            return ApiResponse::error('PROJECT_NOT_FOUND', 'Project not found.', 404);
        }

        return ApiResponse::success($projectDomain);
    }

    public function update(UpdateProjectRequest $request, int $project): JsonResponse
    {
        $projectDomain = $this->projectRepository->findById($project);

        if ($projectDomain === null) {
            return ApiResponse::error('PROJECT_NOT_FOUND', 'Project not found.', 404);
        }

        $updatedProject = new Project(
            id: $projectDomain->id,
            ownerId: $projectDomain->ownerId,
            name: (string) ($request->validated('name') ?? $projectDomain->name),
            description: $request->validated('description') ?? $projectDomain->description,
            status: isset($request->validated()['status'])
                ? \Src\Domain\Project\ProjectStatus::from((string) $request->validated('status'))
                : $projectDomain->status,
        );

        $this->projectRepository->update($updatedProject);

        Cache::forget('projects:' . $request->user()->id . ':list');

        return ApiResponse::success($updatedProject);
    }

    public function destroy(int $project): JsonResponse
    {
        $projectDomain = $this->projectRepository->findById($project);

        if ($projectDomain === null) {
            return ApiResponse::error('PROJECT_NOT_FOUND', 'Project not found.', 404);
        }

        // Delete related tasks first
        \Src\Infrastructure\Persistence\Eloquent\Models\TaskModel::query()
            ->where('project_id', $project)
            ->delete();

        // Delete the project via Eloquent model directly
        \Src\Infrastructure\Persistence\Eloquent\Models\ProjectModel::query()
            ->where('id', $project)
            ->delete();

        Cache::forget('projects:' . request()->user()->id . ':list');

        return ApiResponse::success(null, 204);
    }
}
