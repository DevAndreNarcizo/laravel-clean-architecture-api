<?php

declare(strict_types=1);

namespace Src\Application\Project;

final readonly class CreateProjectData
{
    public function __construct(
        public int $ownerId,
        public string $name,
        public ?string $description,
    ) {}
}
