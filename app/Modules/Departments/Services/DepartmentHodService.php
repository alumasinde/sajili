<?php

declare(strict_types=1);

namespace App\Modules\Departments\Services;

use App\Modules\Departments\Repositories\DepartmentHodRepository;
use App\Modules\Departments\Repositories\DepartmentRepository;
use App\Modules\Roles\Repositories\RoleRepository;
use App\Modules\Users\Repositories\UserRepository;
use InvalidArgumentException;

final class DepartmentHodService
{
    public function __construct(
        private readonly DepartmentRepository $departments,
        private readonly DepartmentHodRepository $hods,
        private readonly UserRepository $users,
        private readonly RoleRepository $roles,
    ) {}

    public function assign(int $departmentId, int $userId): void
    {
        $department = $this->departments->findById($departmentId);
        $user = $this->users->findById($userId);

        if ($department === null) {
            throw new InvalidArgumentException('Department not found.');
        }

        if ($user === null) {
            throw new InvalidArgumentException('User not found.');
        }

        if ((int) ($user['department_id'] ?? 0) !== $departmentId) {
            throw new InvalidArgumentException(
                'The HOD must belong to the department being assigned.'
            );
        }

        $hodRole = $this->roles->findBySlug('hod');

        if ($hodRole === null) {
            throw new InvalidArgumentException(
                'The default HOD role has not been seeded for this organization.'
            );
        }

        $this->hods->assign($departmentId, $userId);
        $this->roles->assignToUser($userId, (int) $hodRole['id']);
    }
}
