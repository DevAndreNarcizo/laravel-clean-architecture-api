<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_refresh_and_logout(): void
    {
        $user = User::factory()->create(['email' => 'andre@example.com']);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'andre@example.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['access_token', 'refresh_token', 'expires_in']]);

        $refreshToken = $login->json('data.refresh_token');

        $refresh = $this->postJson('/api/v1/auth/refresh', [
            'refresh_token' => $refreshToken,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson('/api/v1/auth/me', [
            'Authorization' => 'Bearer '.$refresh->json('data.access_token'),
        ])
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);

        $this->postJson('/api/v1/auth/logout', [
            'refresh_token' => $refresh->json('data.refresh_token'),
        ])->assertOk();
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'andre@example.com']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'andre@example.com',
            'password' => 'wrong',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'AUTH_INVALID_CREDENTIALS');
    }
}
