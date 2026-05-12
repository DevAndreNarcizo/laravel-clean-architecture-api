<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Infrastructure\Persistence\Eloquent\Models\ProjectModel;
use Tests\TestCase;

final class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function testTaskCanBeCreatedForProject(): void
    {
        $user = User::factory()->create();
        $project = ProjectModel::query()->create([
            'owner_id' => $user->id,
            'name' => 'Platform',
            'status' => 'active',
        ]);

        $this->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title' => 'Write OpenAPI contract',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Write OpenAPI contract');
    }

    public function testTaskRequiresExistingProject(): void
    {
        $this->postJson('/api/v1/projects/999/tasks', ['title' => 'Invalid'])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'TASK_CREATE_FAILED');
    }
}
