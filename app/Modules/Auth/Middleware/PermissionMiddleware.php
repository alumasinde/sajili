<?php

declare(strict_types=1);

namespace App\Modules\Auth\Middleware;

use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Roles\Repositories\RoleRepository;

final class PermissionMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly RoleRepository $roles) {}

    public function handle(Request $request, Response $response, callable $next): void
    {
        $permission = (string) $request->attribute('required_permission', '');
        $user = $request->attribute('auth_user');

        if (!is_array($user)) {
            $response->json([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Authentication is required.',
                ],
            ], 401);
        }

        if ($permission === '') {
            $next();
            return;
        }

        $permissions = $this->roles->userPermissions((int) $user['id']);

        if (!in_array($permission, $permissions, true)) {
            $response->json([
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'You do not have permission to perform this action.',
                ],
            ], 403);
        }

        $next();
    }
}
