<?php

namespace App\Services\Auth;

use App\Enums\AuditActionEnum;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    /**
     * Attempt login dengan email ATAU username.
     *
     * @throws AuthenticationException jika kredensial salah atau akun nonaktif
     */
    public function attempt(string $login, string $password, bool $remember = false): User
    {
        $user = $this->findUser($login);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new AuthenticationException('Kredensial tidak valid.');
        }

        if (! $user->is_active) {
            throw new AuthenticationException('Akun Anda tidak aktif. Hubungi administrator.');
        }

        Auth::login($user, $remember);

        AuditLogger::logAuth(AuditActionEnum::Login, $user);

        return $user;
    }

    public function logout(): void
    {
        $user = Auth::user();

        if ($user) {
            AuditLogger::logAuth(AuditActionEnum::Logout, $user);
        }

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    /**
     * Cari user berdasarkan email atau username.
     * Jika input mengandung '@' → cari di kolom email.
     * Selain itu → cari di kolom username (case-insensitive).
     */
    private function findUser(string $login): ?User
    {
        $column = str_contains($login, '@') ? 'email' : 'username';

        return User::where($column, strtolower($login))
            ->with('role.permissions')
            ->first();
    }
}
