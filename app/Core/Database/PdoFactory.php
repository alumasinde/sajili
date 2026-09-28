<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDO;
use PDOException;

final class PdoFactory
{
    public static function make(ConnectionConfig $config): PDO
    {
        try {
            return new PDO($config->dsn(), $config->username, $config->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]);
        } catch (PDOException $e) {
            throw new PDOException('Database connection failed.', (int) $e->getCode(), $e);
        }
    }
}
