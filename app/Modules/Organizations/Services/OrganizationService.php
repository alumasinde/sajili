<?php

declare(strict_types=1);

namespace App\Modules\Organizations\Services;

use App\Core\Security\Encryption;
use App\Modules\Organizations\Repositories\DomainRepository;
use App\Modules\Organizations\Repositories\OrganizationRepository;
use InvalidArgumentException;
use PDO;

final class OrganizationService
{
    public function __construct(
        private readonly PDO $platform,
        private readonly OrganizationRepository $organizations,
        private readonly DomainRepository $domains,
    ) {
    }

    public function create(array $input): int
    {
        $name = trim((string) ($input['name'] ?? ''));
        $slug = strtolower(trim((string) ($input['slug'] ?? '')));
        $domain = strtolower(trim((string) ($input['domain'] ?? '')));
        $mode = strtolower(trim((string) ($input['database_mode'] ?? 'shared')));

        if ($name === '' || $slug === '' || $domain === '') {
            throw new InvalidArgumentException('Name, slug and domain are required.');
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new InvalidArgumentException('Slug must contain lowercase letters, numbers and hyphens only.');
        }

        if (!filter_var('https://' . $domain, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid domain.');
        }

        if (!in_array($mode, ['shared', 'dedicated'], true)) {
            throw new InvalidArgumentException('Database mode must be shared or dedicated.');
        }

        $databaseConfigEncrypted = null;

        if ($mode === 'dedicated') {
            $databaseConfig = [
                'driver' => 'mysql',
                'host' => trim((string) ($input['database_host'] ?? '')),
                'port' => (int) ($input['database_port'] ?? 3306),
                'database' => trim((string) ($input['database_name'] ?? '')),
                'username' => trim((string) ($input['database_username'] ?? '')),
                'password' => (string) ($input['database_password'] ?? ''),
            ];

            if ($databaseConfig['host'] === '' || $databaseConfig['database'] === '' || $databaseConfig['username'] === '' || $databaseConfig['password'] === '') {
                throw new InvalidArgumentException('Dedicated database credentials are required.');
            }

            $databaseConfigEncrypted = Encryption::encrypt(
                json_encode($databaseConfig, JSON_THROW_ON_ERROR)
            );
        }

        $this->platform->beginTransaction();

        return $this->createTransaction($name, $slug, $domain, $mode, $databaseConfigEncrypted);
    }

    private function createTransaction(
        string $name,
        string $slug,
        string $domain,
        string $mode,
        ?string $databaseConfigEncrypted,
    ): int {
        try {
            $id = $this->organizations->create([
                'public_id' => bin2hex(random_bytes(16)),
                'slug' => $slug,
                'name' => $name,
                'status' => 'active',
                'database_mode' => $mode,
                'database_config_encrypted' => $databaseConfigEncrypted,
            ]);

            $this->domains->create($id, $domain, true);
            $this->platform->commit();

            return $id;
        } catch (\Throwable $e) {
            if ($this->platform->inTransaction()) {
                $this->platform->rollBack();
            }

            throw $e;
        }
    }
}
