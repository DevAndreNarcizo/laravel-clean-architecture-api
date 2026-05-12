<?php

declare(strict_types=1);

namespace Src\Domain\Project;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Done = 'done';
}
