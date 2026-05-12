<?php

declare(strict_types=1);

namespace Src\Domain\Project;

interface ProjectRepository
{
    public function save(Project $project): Project;

    public function findById(int $id): ?Project;
}
