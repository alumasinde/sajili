<?php

declare(strict_types=1);

namespace App\Core\Tenancy;

use App\Core\Http\Middleware\MiddlewareInterface;
use App\Core\Http\Request;
use App\Core\Http\Response;
use RuntimeException;

final class TenantMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly TenantResolver $resolver,
        private readonly TenantContext $context,
    ) {
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $organization = $this->resolver->resolveHost($request->host());

        if ($organization === null) {
            $response->json([
                'error' => [
                    'code' => 'TENANT_NOT_FOUND',
                    'message' => 'The requested company could not be resolved.',
                ],
            ], 404);
        }

        try {
            $this->context->set($organization);
            $next();
        } finally {
            $this->context->clear();
        }
    }
}
