<?php

declare(strict_types=1);

namespace Src\Domain\Project;

enum ProjectStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
}
