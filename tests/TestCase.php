<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Src\Application\Auth\JwtTokenService;

abstract class TestCase extends BaseTestCase
{
    //

    protected function bearerTokenFor(User $user): string
    {
        return app(JwtTokenService::class)->issue((int) $user->id);
    }
}
