<?php

declare(strict_types=1);

namespace App\Modules\Roles\Services;

use App\Modules\Access\Repositories\PermissionRepository;
use App\Modules\Roles\Repositories\RoleRepository;

final class RoleService
{
    public function __construct(
        private readonly RoleRepository $roles,
        private readonly PermissionRepository $permissions,
    ) {}

    public function create(array $input): int
    {
        $name = trim((string) ($input['name'] ?? ''));
        $slug = strtolower(trim((string) ($input['slug'] ?? '')));
        $description = trim((string) ($input['description'] ?? ''));

        if ($name === '' || $slug === '') {
            throw new \InvalidArgumentException('Role name and slug are required.');
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new \InvalidArgumentException('Role slug is invalid.');
        }

        if ($this->roles->findBySlug($slug) !== null) {
            throw new \InvalidArgumentException('A role with this slug already exists.');
        }

        $roleId = $this->roles->create([
            'public_id' => bin2hex(random_bytes(16)),
            'name' => $name,
            'slug' => $slug,
            'description' => $description !== '' ? $description : null,
            'status' => 'active',
        ]);

        return $roleId;
    }

    public function seedDefaults(): void
    {
        $defaults = [
            [
                'name' => 'HR Administrator',
                'slug' => 'hr-admin',
                'description' => 'Manage HR users, departments and employee onboarding.',
                'permissions' => [
                    'dashboard.view', 'users.view', 'users.create', 'users.update',
                    'users.deactivate', 'roles.view', 'roles.manage', 'departments.view',
                    'departments.manage', 'departments.assign_hod',
                    'employees.view', 'employees.create', 'employees.update',
                    'employees.import', 'onboarding.view', 'onboarding.create',
                ],
            ],
            [
                'name' => 'IT Administrator',
                'slug' => 'it-admin',
                'description' => 'Manage IT users, assets and onboarding handovers.',
                'permissions' => [
                    'dashboard.view', 'users.view', 'users.create', 'users.update',
                    'roles.view', 'roles.manage', 'departments.view', 'employees.view',
                    'onboarding.view', 'onboarding.approve', 'assets.view',
                    'assets.create', 'assets.assign', 'assets.return',
                ],
            ],
            [
                'name' => 'HOD',
                'slug' => 'hod',
                'description' => 'Review and approve onboarding for a department.',
                'permissions' => [
                    'dashboard.view', 'employees.view', 'onboarding.view',
                    'onboarding.approve',
                ],
            ],
            [
                'name' => 'Employee',
                'slug' => 'employee',
                'description' => 'Review and sign own onboarding handover.',
                'permissions' => [
                    'onboarding.view', 'onboarding.sign',
                ],
            ],
        ];

        foreach ($defaults as $definition) {
            $role = $this->roles->findBySlug($definition['slug']);

            if ($role === null) {
                $roleId = $this->roles->create([
                    'public_id' => bin2hex(random_bytes(16)),
                    'name' => $definition['name'],
                    'slug' => $definition['slug'],
                    'description' => $definition['description'],
                    'status' => 'active',
                ]);
            } else {
                $roleId = (int) $role['id'];
            }

            $permissionRows = $this->permissions->findByNames($definition['permissions']);

            foreach ($permissionRows as $permission) {
                $this->roles->assignPermission($roleId, (int) $permission['id']);
            }
        }
    }
}
