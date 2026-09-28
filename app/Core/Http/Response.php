<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Response
{
    public function html(string $html, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');
        echo $html;
        exit;
    }

    public function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');

        echo json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    public function redirect(string $location, int $status = 302): never
    {
        header('Location: ' . $location, true, $status);
        exit;
    }
}
