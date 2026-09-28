<?php

declare(strict_types=1);

namespace App\Core\Database;

use App\Core\Support\Env;
use PDO;

final class DatabaseManager
{
    private ?PDO $platform = null;
    private ?PDO $shared = null;

    public function platform(): PDO
    {
        return $this->platform ??= PdoFactory::make($this->configFromEnv('PLATFORM_DB'));
    }

    public function shared(): PDO
    {
        return $this->shared ??= PdoFactory::make($this->configFromEnv('DB'));
    }

    public function fromConfig(ConnectionConfig $config): PDO
    {
        return PdoFactory::make($config);
    }

    private function configFromEnv(string $prefix): ConnectionConfig
    {
        return new ConnectionConfig(
            driver: Env::get($prefix . '_CONNECTION', Env::get('DB_CONNECTION', 'mysql')),
            host: Env::get($prefix . '_HOST', '127.0.0.1'),
            port: Env::int($prefix . '_PORT', 3306),
            database: Env::get($prefix . '_DATABASE', 'onboarding_platform'),
            username: Env::get($prefix . '_USERNAME', 'root'),
            password: Env::get($prefix . '_PASSWORD', ''),
        );
    }
}
