<?php

declare(strict_types=1);

namespace Src\Domain\Project;

interface ProjectRepository
{
    public function save(Project $project): Project;

    public function update(Project $project): Project;

    public function findById(int $id): ?Project;

    /**
     * @return array{projects: Project[], pagination: array{current_page: int, per_page: int, total: int, last_page: int}}
     */
    public function findByOwner(int $ownerId, ?string $search = null, ?string $status = null, string $sortBy = 'created_at', string $sortDir = 'desc', int $perPage = 15): array;
}
