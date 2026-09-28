<?php

declare(strict_types=1);

namespace App\Modules\Roles\Repositories;

use App\Core\Tenancy\TenantContext;

final class RoleRepository
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function all(): array
    {
        return $this->tenant->connection()->query(
            'SELECT id, public_id, name, slug, description, status
             FROM roles ORDER BY name'
        )->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->tenant->connection()->prepare(
            'SELECT * FROM roles WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->tenant->connection()->prepare(
            'SELECT * FROM roles WHERE slug = :slug LIMIT 1'
        );
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $statement = $this->tenant->connection()->prepare(
            'INSERT INTO roles (public_id, name, slug, description, status)
             VALUES (:public_id, :name, :slug, :description, :status)'
        );
        $statement->execute($data);

        return (int) $this->tenant->connection()->lastInsertId();
    }

    public function assignPermission(int $roleId, int $permissionId): void
    {
        $statement = $this->tenant->connection()->prepare(
            'INSERT IGNORE INTO role_permissions (role_id, permission_id)
             VALUES (:role_id, :permission_id)'
        );
        $statement->execute([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
        ]);
    }

    public function assignToUser(int $userId, int $roleId): void
    {
        $statement = $this->tenant->connection()->prepare(
            'INSERT IGNORE INTO user_roles (user_id, role_id)
             VALUES (:user_id, :role_id)'
        );
        $statement->execute([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }

    public function userRoles(int $userId): array
    {
        $statement = $this->tenant->connection()->prepare(
            'SELECT r.* FROM roles r
             INNER JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = :user_id AND r.status = :status
             ORDER BY r.name'
        );
        $statement->execute(['user_id' => $userId, 'status' => 'active']);

        return $statement->fetchAll();
    }

    public function userPermissions(int $userId): array
    {
        $statement = $this->tenant->connection()->prepare(
            'SELECT DISTINCT p.name
             FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             INNER JOIN user_roles ur ON ur.role_id = rp.role_id
             INNER JOIN roles r ON r.id = rp.role_id
             WHERE ur.user_id = :user_id AND r.status = :status
             ORDER BY p.name'
        );
        $statement->execute(['user_id' => $userId, 'status' => 'active']);

        return array_column($statement->fetchAll(), 'name');
    }
}
