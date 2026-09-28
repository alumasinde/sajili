<?php

declare(strict_types=1);

namespace App\Core\Support;

use Dotenv\Dotenv;

final class Env
{
    private static array $values = [];

    public static function load(string $basePath): void
    {
        if (!is_file($basePath . '/.env')) {
            self::$values = [];
            return;
        }

        $dotenv = Dotenv::createImmutable($basePath);
        self::$values = $dotenv->safeLoad();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$values)) {
            return self::$values[$key];
        }

        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
        return $value ?? $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        return (int) self::get($key, $default);
    }
}
