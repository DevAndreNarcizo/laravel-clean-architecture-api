<?php

declare(strict_types=1);

namespace Src\Interface\Http\Controllers;

use DomainException;
use Illuminate\Http\JsonResponse;
use Src\Application\Project\CreateTaskData;
use Src\Application\Project\CreateTaskUseCase;
use Src\Domain\Project\Task;
use Src\Domain\Project\TaskRepository;
use Src\Domain\Project\TaskStatus;
use Src\Infrastructure\Messaging\TaskCreatedPublisher;
use Src\Interface\Http\Requests\StoreTaskRequest;
use Src\Interface\Http\Requests\UpdateTaskRequest;
use Src\Interface\Http\Resources\ApiResponse;

final readonly class TaskController
{
    public function __construct(
        private CreateTaskUseCase $createTask,
        private TaskCreatedPublisher $publisher,
        private TaskRepository $taskRepository,
    ) {}

    /**
     * Lista todas as tarefas de um projeto.
     */
    public function index(int $project): JsonResponse
    {
        $tasks = $this->taskRepository->findByProjectId($project);

        return ApiResponse::success($tasks);
    }

    /**
     * Exibe uma tarefa específica.
     */
    public function show(int $task): JsonResponse
    {
        $taskDomain = $this->taskRepository->findById($task);

        if ($taskDomain === null) {
            return ApiResponse::error('TASK_NOT_FOUND', 'Task not found.', 404);
        }

        return ApiResponse::success($taskDomain);
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

        $this->publisher->publish($task);

        return ApiResponse::success($task, 201);
    }

    /**
     * Atualiza uma tarefa existente.
     */
    public function update(UpdateTaskRequest $request, int $task): JsonResponse
    {
        $taskDomain = $this->taskRepository->findById($task);

        if ($taskDomain === null) {
            return ApiResponse::error('TASK_NOT_FOUND', 'Task not found.', 404);
        }

        $updatedTask = new Task(
            id: $taskDomain->id,
            projectId: $taskDomain->projectId,
            title: (string) ($request->validated('title') ?? $taskDomain->title),
            description: $request->validated('description') ?? $taskDomain->description,
            status: isset($request->validated()['status'])
                ? TaskStatus::from((string) $request->validated('status'))
                : $taskDomain->status,
        );

        $this->taskRepository->save($updatedTask);

        return ApiResponse::success($updatedTask);
    }

    /**
     * Remove uma tarefa.
     */
    public function destroy(int $task): JsonResponse
    {
        $taskDomain = $this->taskRepository->findById($task);

        if ($taskDomain === null) {
            return ApiResponse::error('TASK_NOT_FOUND', 'Task not found.', 404);
        }

        $this->taskRepository->delete($task);

        return ApiResponse::success(null, 204);
    }
}
