<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Support\Env;
use RuntimeException;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (headers_sent()) {
            throw new RuntimeException('Cannot start session after headers have been sent.');
        }

        session_name((string) Env::get('SESSION_NAME', 'onboarding_session'));

        session_set_cookie_params([
            'lifetime' => Env::int('SESSION_LIFETIME', 120) * 60,
            'path' => '/',
            'secure' => Env::bool('SESSION_SECURE', false),
            'httponly' => true,
            'samesite' => Env::get('SESSION_SAME_SITE', 'Lax'),
        ]);

        session_start();
    }

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function put(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function forget(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function clear(): void
    {
        self::start();
        $_SESSION = [];
        session_regenerate_id(true);
    }
}
