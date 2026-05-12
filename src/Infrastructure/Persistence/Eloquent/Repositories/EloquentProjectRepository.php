<?php

declare(strict_types=1);

namespace Src\Infrastructure\Persistence\Eloquent\Repositories;

use Src\Domain\Project\Project;
use Src\Domain\Project\ProjectRepository;
use Src\Domain\Project\ProjectStatus;
use Src\Infrastructure\Persistence\Eloquent\Models\ProjectModel;

final class EloquentProjectRepository implements ProjectRepository
{
    public function save(Project $project): Project
    {
        $model = ProjectModel::query()->create([
            'owner_id' => $project->ownerId,
            'name' => $project->name,
            'description' => $project->description,
            'status' => $project->status->value,
        ]);

        return $this->toDomain($model);
    }

    public function findById(int $id): ?Project
    {
        $model = ProjectModel::query()->find($id);

        return $model instanceof ProjectModel ? $this->toDomain($model) : null;
    }

    private function toDomain(ProjectModel $model): Project
    {
        return new Project(
            id: (int) $model->id,
            ownerId: (int) $model->owner_id,
            name: (string) $model->name,
            description: $model->description,
            status: ProjectStatus::from((string) $model->status),
        );
    }
}
