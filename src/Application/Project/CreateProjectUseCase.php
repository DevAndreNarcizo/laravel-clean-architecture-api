<?php

declare(strict_types=1);

namespace Src\Application\Project;

use Src\Domain\Project\Project;
use Src\Domain\Project\ProjectRepository;

final readonly class CreateProjectUseCase
{
    public function __construct(private ProjectRepository $projects) {}

    /**
     * Cria um projeto mantendo a regra de negócio fora do controller.
     *
     * @author André Narcizo
     */
    public function execute(CreateProjectData $data): Project
    {
        return $this->projects->save(new Project(
            id: null,
            ownerId: $data->ownerId,
            name: $data->name,
            description: $data->description,
        ));
    }
}
