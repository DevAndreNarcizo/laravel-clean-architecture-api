<?php

declare(strict_types=1);

namespace Src\Interface\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Src\Application\Auth\JwtTokenService;
use Src\Interface\Http\Resources\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class JwtAuthenticate
{
    public function __construct(private JwtTokenService $jwt) {}

    /**
     * Autentica requests com Bearer JWT.
     *
     * @author André Narcizo
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if ($token === null) {
            return ApiResponse::error('AUTH_TOKEN_MISSING', 'Bearer token is required.', 401);
        }

        try {
            $claims = $this->jwt->verify($token);
        } catch (\Throwable) {
            return ApiResponse::error('AUTH_TOKEN_INVALID', 'Bearer token is invalid or expired.', 401);
        }

        $user = User::query()->find($claims['sub']);
        if (! $user instanceof User) {
            return ApiResponse::error('AUTH_USER_NOT_FOUND', 'Authenticated user was not found.', 401);
        }

        $request->setUserResolver(static fn (): User => $user);

        return $next($request);
    }
}
