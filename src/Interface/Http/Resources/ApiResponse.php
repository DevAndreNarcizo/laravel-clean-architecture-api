<?php

declare(strict_types=1);

namespace Src\Interface\Http\Resources;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    /** @param array<string, mixed> $meta */
    public static function success(mixed $data, int $status = 200, array $meta = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'error' => null,
            'meta' => ['timestamp' => now()->toISOString(), ...$meta],
        ], $status);
    }

    /** @param array<int|string, mixed> $details */
    public static function error(string $code, string $message, int $status = 400, array $details = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'error' => compact('code', 'message', 'details'),
            'meta' => ['timestamp' => now()->toISOString()],
        ], $status);
    }
}
