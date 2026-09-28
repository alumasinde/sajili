<?php

declare(strict_types=1);

namespace App\Modules\Health\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Support\Logger;

final class HealthController
{
    public function __construct(
        private readonly Request $request,
        private readonly Response $response,
        private readonly Logger $logger,
    ) {
    }

    public function index(): never
    {
        $this->response->html(
            '<!doctype html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Onboarding Platform</title>
                <link rel="stylesheet" href="/assets/css/app.css">
            </head>
            <body>
                <main class="shell">
                    <section class="panel">
                        <span class="eyebrow">PHASE 1 FOUNDATION</span>
                        <h1>Onboarding Platform</h1>
                        <p class="muted">Application foundation is running.</p>
                        <div class="status"><span class="dot"></span> System online</div>
                        <div class="links">
                            <a href="/health">Web health</a>
                            <a href="/api/v1/health">API health</a>
                        </div>
                    </section>
                </main>
            </body>
            </html>'
        );
    }

    public function api(): never
    {
        $this->response->json([
            'data' => [
                'service' => 'onboarding-platform',
                'status' => 'ok',
                'version' => '1.0.0',
            ],
        ]);
    }
}
