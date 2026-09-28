<?php

declare(strict_types=1);

namespace App\Modules\Organizations\Repositories;

use PDO;

final class OrganizationRepository
{
    public function __construct(private readonly PDO $platform)
    {
    }

    public function create(array $data): int
    {
        $statement = $this->platform->prepare(
            'INSERT INTO organizations'
            . '(public_id, slug, name, status, database_mode, database_config_encrypted)'
            . 'VALUES (:public_id, :slug, :name, :status, :database_mode, :database_config_encrypted)'
        );

        $statement->execute($data);
        return (int) $this->platform->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->platform->prepare('SELECT * FROM organizations WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return is_array($row) ? $row : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->platform->prepare('SELECT * FROM organizations WHERE slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();
        return is_array($row) ? $row : null;
    }
}
