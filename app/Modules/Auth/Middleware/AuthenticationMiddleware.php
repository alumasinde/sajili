<?php

declare(strict_types=1);

namespace App\Modules\Auth\Middleware;

use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\Session;
use App\Core\Tenancy\TenantContext;
use App\Modules\Users\Repositories\UserRepository;

final class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly TenantContext $tenant,
    ) {}

    public function handle(Request $request, Response $response, callable $next): void
    {
        Session::start();
        $id = Session::get('auth_user_id');
        $sessionOrganization = Session::get('auth_organization_public_id');
        $currentOrganization = $this->tenant->organization()['public_id'];

        if ($sessionOrganization !== $currentOrganization) {
            Session::clear();
            $this->unauthorized($request, $response);
        }

        if (!is_int($id) && !is_numeric($id)) {
            $this->unauthorized($request, $response);
        }

        $user = $this->users->findById((int) $id);

        if ($user === null || (string) $user['status'] !== 'active') {
            Session::clear();
            $this->unauthorized($request, $response);
        }

        $safeUser = $user;
        unset($safeUser['password_hash']);
        $request->setAttribute('auth_user', $safeUser);
        $next();
    }

    private function unauthorized(Request $request, Response $response): never
    {
        if ($request->pathStartsWith('/api/')) {
            $response->json([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Authentication is required.',
                ],
            ], 401);
        }

        $response->redirect('/login');
    }
}
