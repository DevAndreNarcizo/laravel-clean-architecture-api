<?php

declare(strict_types=1);

namespace Src\Interface\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Src\Application\Auth\JwtTokenValidator;
use Src\Interface\Http\Resources\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class JwtAuthenticate
{
    public function __construct(private JwtTokenValidator $jwt) {}

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
            if ($claims === null) {
                throw new \RuntimeException('Invalid token, claims is null');
            }
        } catch (\Throwable $e) {
            Log::error('JWT authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('AUTH_TOKEN_INVALID', 'Bearer token is invalid or expired.', 401);
        }

        $user = User::query()->find($claims['sub']);
        if (! $user instanceof User) {
            return ApiResponse::error('AUTH_USER_NOT_FOUND', 'Authenticated user was not found.', 401);
        }

        $request->setUserResolver(static fn (): User => $user);
        Auth::guard('api')->setUser($user);

        return $next($request);
    }
}
