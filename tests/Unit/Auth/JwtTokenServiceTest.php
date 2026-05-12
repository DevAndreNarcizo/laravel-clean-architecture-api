<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Src\Application\Auth\JwtTokenService;

final class JwtTokenServiceTest extends TestCase
{
    public function test_it_issues_and_verifies_token(): void
    {
        $service = new JwtTokenService('secret');
        $claims = $service->verify($service->issue(10));

        $this->assertSame(10, $claims['sub']);
    }

    public function test_it_rejects_tampered_token(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $service = new JwtTokenService('secret');
        $token = $service->issue(10);
        $service->verify($token.'tampered');
    }
}
