<?php

namespace App\Services\Auth;

use App\Enums\AuditActionEnum;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    private const REMEMBER_DAYS = 7;

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

        // Auth::login($user, true) secara default membuat remember cookie "forever" (5 tahun).
        // Kita pass false agar tidak membuat cookie forever, lalu kita queue cookie 7-hari sendiri.
        Auth::login($user, false);

        if ($remember) {
            $this->queueRememberCookie7Days($user);
        }

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
     * Buat dan queue remember cookie dengan expiry tepat 7 hari.
     *
     * Laravel Auth::login($user, true) membuat cookie "forever" (5 tahun).
     * Karena kita memanggil Auth::login($user, false), kita perlu:
     * 1. Generate remember token sendiri (sama seperti yang dilakukan Laravel internaly).
     * 2. Queue cookie dengan durasi 7 hari.
     *
     * Format recaller value: "{id}|{remember_token}|{password_hash}"
     * (sama dengan yang digunakan Laravel SessionGuard)
     */
    private function queueRememberCookie7Days(User $user): void
    {
        // Generate dan simpan remember token ke DB (sama seperti Laravel lakukan)
        $token = \Illuminate\Support\Str::random(60);
        $user->setRememberToken($token);
        $user->save();

        // Bangun nilai recaller (format identik dengan Laravel SessionGuard)
        $recallerValue = $user->getAuthIdentifier()
            . '|' . $token
            . '|' . $user->getAuthPassword();

        Cookie::queue(
            Cookie::make(
                name    : Auth::guard('web')->getRecallerName(),
                value   : $recallerValue,
                minutes : self::REMEMBER_DAYS * 24 * 60,
                path    : config('session.path', '/'),
                domain  : config('session.domain'),
                secure  : config('session.secure', false),
                httpOnly: true,
                sameSite: config('session.same_site', 'lax'),
            )
        );
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
