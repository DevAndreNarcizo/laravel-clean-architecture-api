<?php

declare(strict_types=1);

namespace Src\Infrastructure\Persistence\Eloquent\Repositories;

use Src\Domain\Project\Task;
use Src\Domain\Project\TaskRepository;
use Src\Domain\Project\TaskStatus;
use Src\Infrastructure\Persistence\Eloquent\Models\TaskModel;

final class EloquentTaskRepository implements TaskRepository
{
    public function save(Task $task): Task
    {
        $model = TaskModel::query()->create([
            'project_id' => $task->projectId,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status->value,
        ]);

        return new Task(
            id: (int) $model->id,
            projectId: (int) $model->project_id,
            title: (string) $model->title,
            description: $model->description,
            status: TaskStatus::from((string) $model->status),
        );
    }

    public function countByProject(int $projectId): int
    {
        return TaskModel::query()->where('project_id', $projectId)->count();
    }
}
