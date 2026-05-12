<?php

declare(strict_types=1);

namespace Src\Interface\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Application\Project\CreateProjectData;
use Src\Application\Project\CreateProjectUseCase;
use Src\Interface\Http\Requests\StoreProjectRequest;
use Src\Interface\Http\Resources\ApiResponse;

final readonly class ProjectController
{
    public function __construct(private CreateProjectUseCase $createProject)
    {
    }

    /**
     * Cria um projeto e retorna envelope JSON padronizado.
     *
     * @author André Narcizo
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->createProject->execute(new CreateProjectData(
            ownerId: (int) $request->validated('owner_id'),
            name: (string) $request->validated('name'),
            description: $request->validated('description'),
        ));

        return ApiResponse::success($project, 201);
    }
}
