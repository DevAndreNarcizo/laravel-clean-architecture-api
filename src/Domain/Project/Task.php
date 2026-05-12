<?php

declare(strict_types=1);

namespace Src\Domain\Project;

use InvalidArgumentException;

final class Task
{
    /**
     * @author André Narcizo
     */
    public function __construct(
        public readonly ?int $id,
        public readonly int $projectId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly TaskStatus $status = TaskStatus::Todo,
    ) {
        if (trim($this->title) === '') {
            throw new InvalidArgumentException('Task title is required.');
        }
    }
}
