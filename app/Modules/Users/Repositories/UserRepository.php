<?php

declare(strict_types=1);

namespace App\Modules\Users\Repositories;

use App\Core\Tenancy\TenantContext;
use App\Modules\Users\Models\User;

final class UserRepository
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function findById(int $id): ?array
    {
        $statement = $this->tenant->connection()->prepare(
            'SELECT * FROM users WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->tenant->connection()->prepare(
            'SELECT * FROM users WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => strtolower(trim($email))]);
        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $statement = $this->tenant->connection()->prepare(
            'INSERT INTO users
             (public_id, employee_id, department_id, first_name, last_name, email, password_hash, status)
             VALUES
             (:public_id, :employee_id, :department_id, :first_name, :last_name, :email, :password_hash, :status)'
        );
        $statement->execute($data);

        return (int) $this->tenant->connection()->lastInsertId();
    }

    public function updatePasswordHash(int $id, string $hash): void
    {
        $statement = $this->tenant->connection()->prepare(
            'UPDATE users
             SET password_hash = :password_hash, password_changed_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'password_hash' => $hash,
        ]);
    }

    public function updateLastLogin(int $id): void
    {
        $statement = $this->tenant->connection()->prepare(
            'UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

    public function updateStatus(int $id, string $status): void
    {
        $statement = $this->tenant->connection()->prepare(
            'UPDATE users SET status = :status WHERE id = :id'
        );
        $statement->execute(['id' => $id, 'status' => $status]);
    }

    public function list(): array
    {
        return $this->tenant->connection()->query(
            'SELECT u.id, u.public_id, u.first_name, u.last_name, u.email,
                    u.status, u.department_id, d.name AS department_name
             FROM users u
             LEFT JOIN departments d ON d.id = u.department_id
             ORDER BY u.first_name, u.last_name'
        )->fetchAll();
    }

    public function toModel(?array $row): ?User
    {
        return $row ? User::fromRow($row) : null;
    }
}
