<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\Csrf;
use App\Core\Security\Session;
use App\Core\Support\Logger;
use App\Core\Tenancy\TenantContext;
use App\Core\View;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Roles\Repositories\RoleRepository;
use InvalidArgumentException;

final class AuthController
{
    public function __construct(
        private readonly Request $request,
        private readonly Response $response,
        private readonly Logger $logger,
        private readonly TenantContext $tenant,
        private readonly AuthService $auth,
        private readonly View $view,
        private readonly RoleRepository $roles,
    ) {}

    public function showLogin(): void
    {
        if ($this->auth->currentUser() !== null) {
            $this->response->redirect('/admin');
        }

        $organization = $this->tenant->organization();

        $content = $this->view->render('auth.login', [
            'organizationName' => $organization['name'],
            'csrf' => Csrf::token(),
        ]);

        $this->response->html($this->view->render('layouts.auth', [
            'title' => 'Sign in',
            'content' => $content,
        ]));
    }

    public function loginWeb(): void
    {
        if (!Csrf::verify((string) $this->request->input('_csrf'))) {
            $this->response->html('<h1>Invalid security token.</h1>', 419);
        }

        try {
            $this->auth->login(
                (string) $this->request->input('email'),
                (string) $this->request->input('password')
            );

            $this->response->redirect('/admin');
        } catch (InvalidArgumentException $e) {
            $organization = $this->tenant->organization();

            $content = $this->view->render('auth.login', [
                'organizationName' => $organization['name'],
                'csrf' => Csrf::token(),
                'error' => $e->getMessage(),
            ]);

            $this->response->html($this->view->render('layouts.auth', [
                'title' => 'Sign in',
                'content' => $content,
            ]), 422);
        }
    }

    public function loginApi(): never
    {
        try {
            $result = $this->auth->login(
                (string) $this->request->input('email'),
                (string) $this->request->input('password')
            );

            $this->response->json([
                'data' => [
                    'user' => $result['user'],
                    'roles' => $result['roles'],
                    'csrf_token' => Csrf::token(),
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            $this->response->json([
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => $e->getMessage(),
                ],
            ], 422);
        }
    }

    public function me(): never
    {
        $user = $this->request->attribute('auth_user');

        $this->response->json([
            'data' => [
                'user' => $user,
                'roles' => $this->roles->userRoles((int) $user['id']),
            ],
        ]);
    }

    public function logoutWeb(): never
    {
        if (!Csrf::verify((string) $this->request->input('_csrf'))) {
            $this->response->html('<h1>Invalid security token.</h1>', 419);
        }

        $this->auth->logout();
        $this->response->redirect('/login');
    }

    public function logoutApi(): never
    {
        $this->auth->logout();
        $this->response->json(['data' => ['logged_out' => true]]);
    }
}
