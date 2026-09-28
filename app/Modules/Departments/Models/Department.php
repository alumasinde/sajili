<?php

declare(strict_types=1);

namespace App\Modules\Departments\Models;

final readonly class Department
{
    public function __construct(
        public int $id,
        public string $publicId,
        public string $name,
        public string $slug,
        public string $status,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['public_id'],
            (string) $row['name'],
            (string) $row['slug'],
            (string) $row['status'],
        );
    }
}
