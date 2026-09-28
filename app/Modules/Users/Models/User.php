<?php

declare(strict_types=1);

namespace App\Modules\Users\Models;

final readonly class User
{
    public function __construct(
        public int $id,
        public string $publicId,
        public ?int $employeeId,
        public ?int $departmentId,
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $status,
    ) {}

    public function fullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['public_id'],
            isset($row['employee_id']) ? (int) $row['employee_id'] : null,
            isset($row['department_id']) ? (int) $row['department_id'] : null,
            (string) $row['first_name'],
            (string) $row['last_name'],
            (string) $row['email'],
            (string) $row['status'],
        );
    }
}
