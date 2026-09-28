<?php

declare(strict_types=1);

namespace App\Modules\Users\Services;

use App\Modules\Roles\Repositories\RoleRepository;
use App\Modules\Users\Repositories\UserRepository;
use InvalidArgumentException;
use PDOException;

final class UserService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RoleRepository $roles,
    ) {}

    public function create(array $input): int
    {
        $firstName = trim((string) ($input['first_name'] ?? ''));
        $lastName = trim((string) ($input['last_name'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $password = (string) ($input['password'] ?? '');
        $roleSlug = strtolower(trim((string) ($input['role'] ?? 'employee')));
        $departmentId = $input['department_id'] ?? null;

        if ($firstName === '' || $lastName === '') {
            throw new InvalidArgumentException('First name and last name are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('A valid email address is required.');
        }

        if (strlen($password) < 12) {
            throw new InvalidArgumentException('Password must be at least 12 characters.');
        }

        if ($departmentId !== null && (!is_numeric($departmentId) || (int) $departmentId < 1)) {
            throw new InvalidArgumentException('Invalid department.');
        }

        $role = $this->roles->findBySlug($roleSlug);

        if ($role === null) {
            throw new InvalidArgumentException("Role '{$roleSlug}' does not exist.");
        }

        try {
            $id = $this->users->create([
                'public_id' => bin2hex(random_bytes(16)),
                'employee_id' => null,
                'department_id' => $departmentId !== null ? (int) $departmentId : null,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'status' => 'active',
            ]);
        } catch (PDOException $e) {
            if ((int) $e->errorInfo[1] === 1062) {
                throw new InvalidArgumentException('A user with this email already exists.');
            }

            throw $e;
        }

        $this->roles->assignToUser($id, (int) $role['id']);

        return $id;
    }
}
