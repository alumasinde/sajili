<?php

declare(strict_types=1);

namespace App\Core\Support;

final class Logger
{
    public function __construct(private readonly string $file)
    {
        $directory = dirname($this->file);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    private function write(string $level, string $message, array $context): void
    {
        $payload = $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';

        file_put_contents(
            $this->file,
            sprintf("[%s] %s %s%s%s", date('c'), $level, $message, $payload, PHP_EOL),
            FILE_APPEND | LOCK_EX
        );
    }
}
