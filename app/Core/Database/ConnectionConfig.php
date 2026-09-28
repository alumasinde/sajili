<?php

declare(strict_types=1);

namespace App\Core\Database;

final readonly class ConnectionConfig
{
    public function __construct(
        public string $driver,
        public string $host,
        public int $port,
        public string $database,
        public string $username,
        public string $password,
        public string $charset = 'utf8mb4',
    ) {
    }

    public function dsn(): string
    {
        return sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $this->driver,
            $this->host,
            $this->port,
            $this->database,
            $this->charset
        );
    }
}
