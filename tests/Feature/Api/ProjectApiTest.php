<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_can_be_created_with_standard_envelope(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/projects', [
            'name' => 'Clean Architecture API',
            'description' => 'Portfolio project',
        ], ['Authorization' => 'Bearer '.$this->bearerTokenFor($user)])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Clean Architecture API')
            ->assertJsonPath('error', null);
    }

    public function test_project_validation_uses_standard_laravel_errors(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/projects', [], ['Authorization' => 'Bearer '.$this->bearerTokenFor($user)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_project_index_requires_jwt(): void
    {
        $this->getJson('/api/v1/projects')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'AUTH_TOKEN_MISSING');
    }

    public function test_project_index_returns_paginated_envelope(): void
    {
        $user = User::factory()->create();

        $this->getJson('/api/v1/projects', ['Authorization' => 'Bearer '.$this->bearerTokenFor($user)])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total', 'last_page']]);
    }
}
