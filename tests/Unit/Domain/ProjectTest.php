<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Src\Domain\Project\Project;

final class ProjectTest extends TestCase
{
    public function testProjectRequiresName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Project(id: null, ownerId: 1, name: '', description: null);
    }

    public function testProjectCanBeCreated(): void
    {
        $project = new Project(id: null, ownerId: 1, name: 'Enterprise API', description: 'Demo');

        $this->assertSame('Enterprise API', $project->name);
    }
}
