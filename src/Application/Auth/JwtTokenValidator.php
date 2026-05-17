<?php

declare(strict_types=1);

namespace Src\Application\Auth;

use InvalidArgumentException;

final class JwtTokenValidator
{
    public function __construct(private readonly string $secret)
    {
        if ($this->secret === '') {
            throw new InvalidArgumentException('JWT secret is required.');
        }
    }

    /**
     * Valida assinatura, claims obrigatórios e expiração do token.
     *
     * @return array{sub: int, iat: int, exp: int}|null
     */
    public function verify(string $token): ?array
    {
        $segments = explode('.', $token);
        if (count($segments) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $segments;
        $expected = $this->signature($header . '.' . $payload);
        if (! hash_equals($expected, $signature)) {
            return null;
        }

        /** @var array{alg?: string, typ?: string} $headerData */
        $headerData = json_decode($this->base64UrlDecode($header), true, 512, JSON_THROW_ON_ERROR);
        if (($headerData['alg'] ?? '') !== 'HS256') {
            return null;
        }

        /** @var array{sub?: mixed, iat?: mixed, exp?: mixed} $claims */
        $claims = json_decode($this->base64UrlDecode($payload), true, 512, JSON_THROW_ON_ERROR);

        if (! isset($claims['sub'], $claims['iat'], $claims['exp'])) {
            return null;
        }

        if (! is_int($claims['sub']) || ! is_int($claims['iat']) || ! is_int($claims['exp'])) {
            return null;
        }

        if ($claims['exp'] < time()) {
            return null;
        }

        /** @var array{sub: int, iat: int, exp: int} $claims */
        return $claims;
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
