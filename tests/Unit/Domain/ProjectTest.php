<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Src\Domain\Project\Project;

final class ProjectTest extends TestCase
{
    public function test_project_requires_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Project(id: null, ownerId: 1, name: '', description: null);
    }

    public function test_project_can_be_created(): void
    {
        $project = new Project(id: null, ownerId: 1, name: 'Enterprise API', description: 'Demo');

        $this->assertSame('Enterprise API', $project->name);
    }
}
