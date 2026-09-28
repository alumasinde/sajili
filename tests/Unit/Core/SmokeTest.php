<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    public function testFoundationIsReachable(): void
    {
        self::assertTrue(class_exists(\App\Core\Routing\Router::class));
        self::assertTrue(class_exists(\App\Core\Database\Database::class));
        self::assertTrue(class_exists(\App\Core\Security\Csrf::class));
    }
}
