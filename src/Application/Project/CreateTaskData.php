<?php

declare(strict_types=1);

namespace Src\Application\Project;

final readonly class CreateTaskData
{
    public function __construct(
        public int $projectId,
        public string $title,
        public ?string $description,
    ) {
    }
}
