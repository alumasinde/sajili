<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Support\Env;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name(Env::get('SESSION_NAME', 'onboarding_session'));

        session_set_cookie_params([
            'lifetime' => Env::int('SESSION_LIFETIME', 120) * 60,
            'path' => '/',
            'secure' => Env::bool('SESSION_SECURE', false),
            'httponly' => Env::bool('SESSION_HTTP_ONLY', true),
            'samesite' => Env::get('SESSION_SAME_SITE', 'Lax'),
        ]);

        session_start();
    }
}
