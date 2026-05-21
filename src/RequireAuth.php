<?php

declare(strict_types=1);

namespace BSSN\ConnectIDN;

class RequireAuth
{
    public static function guard(string $loginPath = '/auth/login'): void
    {
        // Prevent open redirect — hanya izinkan path relatif
        if (!str_starts_with($loginPath, '/') || str_contains($loginPath, '//')) {
            $loginPath = '/auth/login';
        }

        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_secure', '1');
            ini_set('session.cookie_samesite', 'Lax');
            session_start();
        }

        if (empty($_SESSION['connectidn_user'])) {
            header('Location: ' . $loginPath);
            exit;
        }
    }

    public static function user(): ?array
    {
        return $_SESSION['connectidn_user'] ?? null;
    }

    public static function store(ConnectIDNUser $user): void
    {
        // Cegah session fixation dengan regenerasi ID setelah autentikasi berhasil
        session_regenerate_id(true);
        $_SESSION['connectidn_user'] = $user->toArray();
        $_SESSION['connectidn_id_token'] = $user->idToken;
    }

    public static function clear(): void
    {
        unset($_SESSION['connectidn_user'], $_SESSION['connectidn_id_token']);
    }
}
