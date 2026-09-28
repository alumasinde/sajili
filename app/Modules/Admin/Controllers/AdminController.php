<?php

declare(strict_types=1);

namespace App\Modules\Admin\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\Csrf;
use App\Core\View;
use App\Modules\Departments\Repositories\DepartmentRepository;
use App\Modules\Departments\Services\DepartmentService;
use App\Modules\Departments\Services\DepartmentHodService;
use App\Modules\Roles\Repositories\RoleRepository;
use App\Modules\Users\Repositories\UserRepository;
use App\Modules\Users\Services\UserService;
use App\Modules\Roles\Services\RoleService;
use InvalidArgumentException;

final class AdminController
{
    public function __construct(
        private readonly Request $request,
        private readonly Response $response,
        private readonly View $view,
        private readonly UserRepository $users,
        private readonly DepartmentRepository $departments,
        private readonly RoleRepository $roles,
        private readonly DepartmentService $departmentService,
        private readonly DepartmentHodService $hodService,
        private readonly UserService $userService,
        private readonly RoleService $roleService,
    ) {}

    public function dashboard(): void
    {
        $content = $this->view->render('admin.dashboard', [
            'user' => $this->request->attribute('auth_user'),
            'userCount' => count($this->users->list()),
            'departmentCount' => count($this->departments->all()),
            'roleCount' => count($this->roles->all()),
            'csrf' => Csrf::token(),
        ]);

        $this->response->html($this->view->render('layouts.admin', [
            'title' => 'Administration',
            'content' => $content,
        ]));
    }

    public function users(): never
    {
        $this->response->json(['data' => $this->users->list()]);
    }

    public function departments(): never
    {
        $this->response->json(['data' => $this->departments->all()]);
    }

    public function roles(): never
    {
        $this->response->json(['data' => $this->roles->all()]);
    }
    public function createDepartment(): never
    {
        try {
            $id = $this->departmentService->create($this->request->all());
            $this->response->json(['data' => ['id' => $id]], 201);
        } catch (InvalidArgumentException $e) {
            $this->response->json([
                'error' => ['code' => 'VALIDATION_ERROR', 'message' => $e->getMessage()],
            ], 422);
        }
    }

    public function assignHod(int $departmentId): never
    {
        $userId = (int) $this->request->input('user_id');

        if ($userId < 1) {
            $this->response->json([
                'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'user_id is required.'],
            ], 422);
        }

        try {
            $this->hodService->assign($departmentId, $userId);
            $this->response->json(['data' => ['assigned' => true]]);
        } catch (InvalidArgumentException $e) {
            $this->response->json([
                'error' => ['code' => 'VALIDATION_ERROR', 'message' => $e->getMessage()],
            ], 422);
        }
    }

    public function createUser(): never
    {
        try {
            $id = $this->userService->create($this->request->all());
            $this->response->json(['data' => ['id' => $id]], 201);
        } catch (InvalidArgumentException $e) {
            $this->response->json([
                'error' => ['code' => 'VALIDATION_ERROR', 'message' => $e->getMessage()],
            ], 422);
        }
    }

    public function updateUserStatus(int $userId): never
    {
        $status = strtolower(trim((string) $this->request->input('status')));

        if (!in_array($status, ['active', 'inactive'], true)) {
            $this->response->json([
                'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Status must be active or inactive.'],
            ], 422);
        }

        $this->users->updateStatus($userId, $status);
        $this->response->json(['data' => ['updated' => true]]);
    }

    public function createRole(): never
    {
        try {
            $id = $this->roleService->create($this->request->all());
            $this->response->json(['data' => ['id' => $id]], 201);
        } catch (InvalidArgumentException $e) {
            $this->response->json([
                'error' => ['code' => 'VALIDATION_ERROR', 'message' => $e->getMessage()],
            ], 422);
        }
    }

}


