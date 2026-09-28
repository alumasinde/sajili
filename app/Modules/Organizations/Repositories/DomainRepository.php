<?php

declare(strict_types=1);

namespace App\Modules\Organizations\Repositories;

use PDO;

final class DomainRepository
{
    public function __construct(private readonly PDO $platform)
    {
    }

    public function create(int $organizationId, string $domain, bool $primary = false): int
    {
        $statement = $this->platform->prepare(
            'INSERT INTO organization_domains (organization_id, domain, is_primary, status)'
            . 'VALUES (:organization_id, :domain, :is_primary, :status)'
        );

        $statement->execute([
            'organization_id' => $organizationId,
            'domain' => strtolower($domain),
            'is_primary' => $primary ? 1 : 0,
            'status' => 'active',
        ]);

        return (int) $this->platform->lastInsertId();
    }
}
