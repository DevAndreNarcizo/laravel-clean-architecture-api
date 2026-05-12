<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ProjectValidationMatrixTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('invalidProjectNames')]
    public function test_project_name_validation_matrix(mixed $name): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/projects', [
            'name' => $name,
        ], ['Authorization' => 'Bearer '.$this->bearerTokenFor($user)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public static function invalidProjectNames(): array
    {
        $cases = [
            'missing' => [null],
            'empty' => [''],
            'array' => [['invalid']],
            'boolean' => [true],
        ];

        for ($i = 1; $i <= 100; $i++) {
            $cases['too-long-'.$i] = [str_repeat('x', 120 + $i)];
        }

        return $cases;
    }
}
