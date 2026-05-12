<?php

declare(strict_types=1);

namespace Src\Domain\Project;

use InvalidArgumentException;

final class Project
{
    /**
     * @author André Narcizo
     */
    public function __construct(
        public readonly ?int $id,
        public readonly int $ownerId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ProjectStatus $status = ProjectStatus::Active,
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException('Project name is required.');
        }
    }
}
