<?php

declare(strict_types=1);

namespace Src\Domain\Project;

interface TaskRepository
{
    public function save(Task $task): Task;

    public function findById(int $id): ?Task;

    /**
     * @return Task[]
     */
    public function findByProjectId(int $projectId): array;

    public function delete(int $id): void;

    public function countByProject(int $projectId): int;
}
