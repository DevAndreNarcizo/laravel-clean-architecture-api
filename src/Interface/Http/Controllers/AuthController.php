<?php

declare(strict_types=1);

namespace Src\Interface\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Src\Application\Auth\JwtTokenService;
use Src\Application\Auth\RefreshTokenService;
use Src\Interface\Http\Requests\LoginRequest;
use Src\Interface\Http\Requests\RefreshTokenRequest;
use Src\Interface\Http\Resources\ApiResponse;

final readonly class AuthController
{
    public function __construct(
        private JwtTokenService $jwt,
        private RefreshTokenService $refreshTokens,
    ) {}

    /**
     * Realiza login com JWT curto e refresh token rotativo.
     *
     * @author André Narcizo
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->first();
        if (! $user instanceof User || ! Hash::check((string) $request->validated('password'), $user->password)) {
            return ApiResponse::error('AUTH_INVALID_CREDENTIALS', 'Invalid credentials.', 401);
        }

        return $this->tokenResponse($user);
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $user = $this->refreshTokens->rotate((string) $request->validated('refresh_token'));
        } catch (\Throwable) {
            return ApiResponse::error('AUTH_REFRESH_INVALID', 'Invalid refresh token.', 401);
        }

        return $this->tokenResponse($user);
    }

    public function logout(RefreshTokenRequest $request): JsonResponse
    {
        $this->refreshTokens->revoke((string) $request->validated('refresh_token'));

        return ApiResponse::success(['logged_out' => true]);
    }

    public function me(): JsonResponse
    {
        return ApiResponse::success(request()->user());
    }

    private function tokenResponse(User $user): JsonResponse
    {
        return ApiResponse::success([
            'token_type' => 'Bearer',
            'access_token' => $this->jwt->issue((int) $user->id),
            'expires_in' => 900,
            'refresh_token' => $this->refreshTokens->issue($user),
        ]);
    }
}
