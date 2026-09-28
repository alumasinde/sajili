<?php

declare(strict_types=1);

namespace App\Modules\Access\Repositories;

use App\Core\Tenancy\TenantContext;

final class PermissionRepository
{
    public function __construct(private readonly TenantContext $tenant)
    {
    }

    public function findByNames(array $names): array
    {
        if ($names === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($names), '?'));
        $statement = $this->tenant->connection()->prepare(
            "SELECT * FROM permissions WHERE name IN ({$placeholders}) ORDER BY name"
        );
        $statement->execute(array_values($names));
        return $statement->fetchAll();
    }

    public function all(): array
    {
        return $this->tenant->connection()
            ->query('SELECT * FROM permissions ORDER BY name')
            ->fetchAll();
    }
}
