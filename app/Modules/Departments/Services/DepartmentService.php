<?php

declare(strict_types=1);

namespace App\Modules\Departments\Services;

use App\Modules\Departments\Repositories\DepartmentRepository;
use InvalidArgumentException;
use PDOException;

final class DepartmentService
{
    public function __construct(private readonly DepartmentRepository $departments) {}

    public function create(array $input): int
    {
        $name = trim((string) ($input['name'] ?? ''));
        $slug = strtolower(trim((string) ($input['slug'] ?? '')));

        if ($name === '') {
            throw new InvalidArgumentException('Department name is required.');
        }

        if ($slug === '') {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name) ?? '', '-'));
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new InvalidArgumentException('Department slug is invalid.');
        }

        if ($this->departments->findBySlug($slug) !== null) {
            throw new InvalidArgumentException('A department with this slug already exists.');
        }

        try {
            return $this->departments->create([
                'public_id' => bin2hex(random_bytes(16)),
                'name' => $name,
                'slug' => $slug,
                'status' => 'active',
            ]);
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                throw new InvalidArgumentException('A department with this name or slug already exists.');
            }

            throw $e;
        }
    }
}
