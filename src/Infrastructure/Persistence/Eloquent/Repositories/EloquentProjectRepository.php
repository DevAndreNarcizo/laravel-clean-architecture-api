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
        if ($project->id !== null) {
            $this->update($project);
            return $project;
        }

        $model = ProjectModel::query()->create([
            'owner_id' => $project->ownerId,
            'name' => $project->name,
            'description' => $project->description,
            'status' => $project->status->value,
        ]);

        return $this->toDomain($model);
    }

    public function update(Project $project): Project
    {
        ProjectModel::query()->where('id', $project->id)->update([
            'name' => $project->name,
            'description' => $project->description,
            'status' => $project->status->value,
        ]);

        return $project;
    }

    public function findById(int $id): ?Project
    {
        $model = ProjectModel::query()->find($id);

        return $model instanceof ProjectModel ? $this->toDomain($model) : null;
    }

    /**
     * @return array{projects: Project[], pagination: array{current_page: int, per_page: int, total: int, last_page: int}}
     */
    public function findByOwner(int $ownerId, ?string $search = null, ?string $status = null, string $sortBy = 'created_at', string $sortDir = 'desc', int $perPage = 15): array
    {
        $allowedSortFields = ['id', 'name', 'created_at', 'updated_at'];
        $sortBy = in_array($sortBy, $allowedSortFields, true) ? $sortBy : 'created_at';
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc'], true) ? strtolower($sortDir) : 'desc';

        $query = ProjectModel::query()
            ->where('owner_id', $ownerId);

        if ($search !== null && $search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $query->orderBy($sortBy, $sortDir);

        $paginator = $query->paginate($perPage);

        $projects = array_map(fn (ProjectModel $model): Project => $this->toDomain($model), $paginator->items());

        return [
            'projects' => $projects,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
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
