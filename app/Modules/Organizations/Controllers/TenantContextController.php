<?php

declare(strict_types=1);

namespace App\Modules\Organizations\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Support\Logger;
use App\Core\Tenancy\TenantContext;

final class TenantContextController
{
    public function __construct(
        private readonly Request $request,
        private readonly Response $response,
        private readonly Logger $logger,
        private readonly TenantContext $context,
    ) {
    }

    public function show(): void
    {
        $organization = $this->context->organization();

        $this->response->json([
            'data' => [
                'organization_id' => (int) $organization['id'],
                'public_id' => $organization['public_id'],
                'name' => $organization['name'],
                'slug' => $organization['slug'],
                'database_mode' => $organization['database_mode'],
                'domain' => $organization['domain'],
            ],
        ]);
    }
}
