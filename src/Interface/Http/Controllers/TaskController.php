<?php

declare(strict_types=1);

namespace Src\Interface\Http\Controllers;

use DomainException;
use Illuminate\Http\JsonResponse;
use Src\Application\Project\CreateTaskData;
use Src\Application\Project\CreateTaskUseCase;
use Src\Interface\Http\Requests\StoreTaskRequest;
use Src\Interface\Http\Resources\ApiResponse;

final readonly class TaskController
{
    public function __construct(private CreateTaskUseCase $createTask)
    {
    }

    /**
     * Cria uma tarefa dentro de um projeto existente.
     *
     * @author André Narcizo
     */
    public function store(StoreTaskRequest $request, int $project): JsonResponse
    {
        try {
            $task = $this->createTask->execute(new CreateTaskData(
                projectId: $project,
                title: (string) $request->validated('title'),
                description: $request->validated('description'),
            ));
        } catch (DomainException $exception) {
            return ApiResponse::error('TASK_CREATE_FAILED', $exception->getMessage(), 422);
        }

        return ApiResponse::success($task, 201);
    }
}
