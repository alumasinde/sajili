<?php

declare(strict_types=1);

namespace App\Core\Database;

use App\Core\Support\Env;
use PDO;

/**
 * Backward-compatible single database facade.
 * Tenant-aware code should use DatabaseManager/TenantContext instead.
 */
final class Database
{
    private ?PDO $connection = null;

    public function connection(): PDO
    {
        return $this->connection ??= PdoFactory::make(new ConnectionConfig(
            driver: Env::get('DB_CONNECTION', 'mysql'),
            host: Env::get('DB_HOST', '127.0.0.1'),
            port: Env::int('DB_PORT', 3306),
            database: Env::get('DB_DATABASE', 'onboarding_tenants'),
            username: Env::get('DB_USERNAME', 'root'),
            password: Env::get('DB_PASSWORD', ''),
        ));
    }

    public function transaction(callable $callback): mixed
    {
        $pdo = $this->connection();
        $pdo->beginTransaction();

        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
