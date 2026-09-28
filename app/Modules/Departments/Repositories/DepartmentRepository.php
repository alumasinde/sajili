<?php

declare(strict_types=1);

namespace App\Modules\Departments\Repositories;

use App\Core\Tenancy\TenantContext;

final class DepartmentRepository
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function all(): array
    {
        return $this->tenant->connection()->query(
            'SELECT * FROM departments ORDER BY name ASC'
        )->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->tenant->connection()->prepare(
            'SELECT * FROM departments WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->tenant->connection()->prepare(
            'SELECT * FROM departments WHERE slug = :slug LIMIT 1'
        );
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $statement = $this->tenant->connection()->prepare(
            'INSERT INTO departments (public_id, name, slug, status)
             VALUES (:public_id, :name, :slug, :status)'
        );
        $statement->execute($data);

        return (int) $this->tenant->connection()->lastInsertId();
    }
}
