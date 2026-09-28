<?php

declare(strict_types=1);

namespace App\Core\Tenancy;

use App\Core\Database\ConnectionConfig;
use App\Core\Database\DatabaseManager;
use App\Core\Security\Encryption;
use PDO;
use RuntimeException;

final class TenantContext
{
    private ?array $organization = null;
    private ?PDO $connection = null;

    public function __construct(private readonly DatabaseManager $databases)
    {
    }

    public function set(array $organization): void
    {
        $this->organization = $organization;
        $this->connection = null;
    }

    public function clear(): void
    {
        $this->organization = null;
        $this->connection = null;
    }

    public function isResolved(): bool
    {
        return $this->organization !== null;
    }

    public function organization(): array
    {
        if ($this->organization === null) {
            throw new RuntimeException('Tenant context has not been resolved.');
        }

        return $this->organization;
    }

    public function organizationId(): int
    {
        return (int) $this->organization()['id'];
    }

    public function connection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $organization = $this->organization();

        if ($organization['database_mode'] === 'shared') {
            return $this->connection = $this->databases->shared();
        }

        $payload = json_decode(
            Encryption::decrypt((string) $organization['database_config_encrypted']),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($payload)) {
            throw new RuntimeException('Invalid dedicated database configuration.');
        }

        $config = new ConnectionConfig(
            driver: (string) ($payload['driver'] ?? 'mysql'),
            host: (string) ($payload['host'] ?? ''),
            port: (int) ($payload['port'] ?? 3306),
            database: (string) ($payload['database'] ?? ''),
            username: (string) ($payload['username'] ?? ''),
            password: (string) ($payload['password'] ?? ''),
        );

        return $this->connection = $this->databases->fromConfig($config);
    }
}
