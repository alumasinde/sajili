<?php

declare(strict_types=1);

namespace App\Core\Tenancy;

use PDO;

final class TenantResolver
{
    public function __construct(private readonly PDO $platform)
    {
    }

    public function resolveHost(string $host): ?array
    {
        $host = strtolower(trim(explode(':', $host)[0]));

        if ($host === '' || strlen($host) > 253) {
            return null;
        }

        $statement = $this->platform->prepare(
            'SELECT o.*, d.domain, d.is_primary\n'
            . 'FROM organization_domains d\n'
            . 'INNER JOIN organizations o ON o.id = d.organization_id\n'
            . 'WHERE d.domain = :domain AND d.status = :status\n'
            . 'LIMIT 1'
        );

        $statement->execute([
            'domain' => $host,
            'status' => 'active',
        ]);

        $organization = $statement->fetch();

        if (!is_array($organization)) {
            return null;
        }

        if ($organization['status'] !== 'active') {
            return null;
        }

        return $organization;
    }
}
