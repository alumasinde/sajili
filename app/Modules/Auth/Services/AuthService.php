<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Core\Security\Session;
use App\Core\Tenancy\TenantContext;
use App\Modules\Roles\Repositories\RoleRepository;
use App\Modules\Users\Repositories\UserRepository;
use InvalidArgumentException;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RoleRepository $roles,
        private readonly TenantContext $tenant,
    ) {}

    public function login(string $email, string $password): array
    {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            throw new InvalidArgumentException('Invalid email or password.');
        }

        $row = $this->users->findByEmail($email);

        if ($row === null || !password_verify($password, (string) $row['password_hash'])) {
            throw new InvalidArgumentException('Invalid email or password.');
        }

        if ((string) $row['status'] !== 'active') {
            throw new InvalidArgumentException('This account is not active.');
        }

        if (password_needs_rehash((string) $row['password_hash'], PASSWORD_DEFAULT)) {
            $this->users->updatePasswordHash(
                (int) $row['id'],
                password_hash($password, PASSWORD_DEFAULT)
            );
        }

        Session::regenerate();
        Session::put('auth_user_id', (int) $row['id']);
        Session::put('auth_user_public_id', (string) $row['public_id']);
        Session::put('auth_organization_public_id', (string) $this->tenant->organization()['public_id']);

        $this->users->updateLastLogin((int) $row['id']);

        return [
            'user' => [
                'id' => (int) $row['id'],
                'public_id' => (string) $row['public_id'],
                'first_name' => (string) $row['first_name'],
                'last_name' => (string) $row['last_name'],
                'email' => (string) $row['email'],
                'status' => (string) $row['status'],
                'department_id' => $row['department_id'] !== null ? (int) $row['department_id'] : null,
            ],
            'roles' => $this->roles->userRoles((int) $row['id']),
        ];
    }

    public function logout(): void
    {
        Session::clear();
    }

    public function currentUser(): ?array
    {
        $id = Session::get('auth_user_id');

        if (!is_int($id) && !is_numeric($id)) {
            return null;
        }

        $user = $this->users->findById((int) $id);

        if ($user === null || (string) $user['status'] !== 'active') {
            return null;
        }

        unset($user['password_hash']);
        return $user;
    }
}
