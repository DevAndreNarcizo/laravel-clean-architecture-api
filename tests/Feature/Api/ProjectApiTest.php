<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function testProjectCanBeCreatedWithStandardEnvelope(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/projects', [
            'owner_id' => $user->id,
            'name' => 'Clean Architecture API',
            'description' => 'Portfolio project',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Clean Architecture API')
            ->assertJsonPath('error', null);
    }

    public function testProjectValidationUsesStandardLaravelErrors(): void
    {
        $this->postJson('/api/v1/projects', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['owner_id', 'name']);
    }
}
