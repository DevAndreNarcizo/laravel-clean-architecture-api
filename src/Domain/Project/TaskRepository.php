<?php

declare(strict_types=1);

namespace Src\Domain\Project;

interface TaskRepository
{
    public function save(Task $task): Task;

    public function countByProject(int $projectId): int;
}
