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
        if ($task->id !== null) {
            $this->updateTask($task);
            return $task;
        }

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

    public function findById(int $id): ?Task
    {
        $model = TaskModel::query()->find($id);

        if (! $model instanceof TaskModel) {
            return null;
        }

        return new Task(
            id: (int) $model->id,
            projectId: (int) $model->project_id,
            title: (string) $model->title,
            description: $model->description,
            status: TaskStatus::from((string) $model->status),
        );
    }

    /**
     * @return Task[]
     */
    public function findByProjectId(int $projectId): array
    {
        $models = TaskModel::query()->where('project_id', $projectId)->get();

        return array_map(fn (TaskModel $model): Task => new Task(
            id: (int) $model->id,
            projectId: (int) $model->project_id,
            title: (string) $model->title,
            description: $model->description,
            status: TaskStatus::from((string) $model->status),
        ), $models->all());
    }

    public function delete(int $id): void
    {
        TaskModel::query()->where('id', $id)->delete();
    }

    public function countByProject(int $projectId): int
    {
        return TaskModel::query()->where('project_id', $projectId)->count();
    }

    private function updateTask(Task $task): void
    {
        TaskModel::query()->where('id', $task->id)->update([
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status->value,
        ]);
    }
}
