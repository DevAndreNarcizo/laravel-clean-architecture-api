<?php

declare(strict_types=1);

namespace Src\Application\Project;

use DomainException;
use Src\Domain\Project\ProjectRepository;
use Src\Domain\Project\Task;
use Src\Domain\Project\TaskRepository;

final readonly class CreateTaskUseCase
{
    public function __construct(
        private ProjectRepository $projects,
        private TaskRepository $tasks,
    ) {}

    /**
     * Cria uma tarefa validando existência do projeto e limite inicial enterprise demo.
     *
     * @author André Narcizo
     */
    public function execute(CreateTaskData $data): Task
    {
        if ($this->projects->findById($data->projectId) === null) {
            throw new DomainException('Project not found.');
        }

        if ($this->tasks->countByProject($data->projectId) >= 100) {
            throw new DomainException('Project task limit reached.');
        }

        return $this->tasks->save(new Task(
            id: null,
            projectId: $data->projectId,
            title: $data->title,
            description: $data->description,
        ));
    }
}
