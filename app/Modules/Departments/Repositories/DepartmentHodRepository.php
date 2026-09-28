<?php

declare(strict_types=1);

namespace App\Modules\Departments\Repositories;

use App\Core\Tenancy\TenantContext;

final class DepartmentHodRepository
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function assign(int $departmentId, int $userId): void
    {
        $statement = $this->tenant->connection()->prepare(
            'INSERT INTO department_hods (department_id, user_id)
             VALUES (:department_id, :user_id)
             ON DUPLICATE KEY UPDATE user_id = VALUES(user_id)'
        );
        $statement->execute([
            'department_id' => $departmentId,
            'user_id' => $userId,
        ]);
    }

    public function findForDepartment(int $departmentId): ?array
    {
        $statement = $this->tenant->connection()->prepare(
            'SELECT u.*
             FROM department_hods h
             INNER JOIN users u ON u.id = h.user_id
             WHERE h.department_id = :department_id
             LIMIT 1'
        );
        $statement->execute(['department_id' => $departmentId]);
        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }
}
