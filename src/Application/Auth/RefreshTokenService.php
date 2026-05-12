<?php

declare(strict_types=1);

namespace Src\Application\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class RefreshTokenService
{
    /**
     * Cria refresh token opaco armazenando apenas hash no banco.
     *
     * @author André Narcizo
     */
    public function issue(User $user, int $ttlDays = 7): string
    {
        $plain = Str::random(80);

        DB::table('refresh_tokens')->insert([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plain),
            'expires_at' => now()->addDays($ttlDays),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $plain;
    }

    /**
     * Rotaciona refresh token e revoga o token anterior.
     *
     * @author André Narcizo
     */
    public function rotate(string $plain): User
    {
        $row = DB::table('refresh_tokens')
            ->where('token_hash', hash('sha256', $plain))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($row === null) {
            throw new RuntimeException('Invalid refresh token.');
        }

        /** @var object{id: int, user_id: int} $row */
        DB::table('refresh_tokens')->where('id', $row->id)->update([
            'revoked_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::query()->find($row->user_id);
        if (! $user instanceof User) {
            throw new RuntimeException('Token user not found.');
        }

        return $user;
    }

    public function revoke(string $plain): void
    {
        DB::table('refresh_tokens')->where('token_hash', hash('sha256', $plain))->update([
            'revoked_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
