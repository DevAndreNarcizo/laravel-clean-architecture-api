<?php

declare(strict_types=1);

namespace Src\Application\Auth;

use InvalidArgumentException;

final class JwtTokenService
{
    public function __construct(private readonly string $secret)
    {
        if ($this->secret === '') {
            throw new InvalidArgumentException('JWT secret is required.');
        }
    }

    /**
     * Gera um JWT HS256 com expiração explícita.
     *
     * @author André Narcizo
     */
    public function issue(int $userId, int $ttlSeconds = 900): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $now = time();
        $payload = [
            'sub' => $userId,
            'iat' => $now,
            'exp' => $now + $ttlSeconds,
        ];

        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR)),
            $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR)),
        ];

        $segments[] = $this->signature($segments[0].'.'.$segments[1]);

        return implode('.', $segments);
    }

    private function signature(string $payload): string
    {
        return $this->base64UrlEncode(hash_hmac('sha256', $payload, $this->secret, true));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/')) ?: '';
    }
}
