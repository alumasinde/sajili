<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDO;
use RuntimeException;

final class MigrationRunner
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function up(string $directory, string $table): int
    {
        if (!is_dir($directory)) {
            throw new RuntimeException("Migration directory does not exist: {$directory}");
        }

        $files = glob(rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.sql') ?: [];
        sort($files, SORT_NATURAL);

        $this->ensureMigrationTable($table);
        $executed = $this->executed($table);
        $batch = $this->nextBatch($table);
        $count = 0;

        foreach ($files as $file) {
            $name = basename($file);

            if (isset($executed[$name])) {
                continue;
            }

            $sql = trim((string) file_get_contents($file));

            if ($sql === '') {
                continue;
            }

            try {
                // MySQL/MariaDB DDL can implicitly commit, so migrations are
                // intentionally not wrapped in a PDO transaction.
                $this->connection->exec($sql);
                $statement = $this->connection->prepare(
                    "INSERT INTO {$this->quoteIdentifier($table)} (migration, batch) VALUES (:migration, :batch)"
                );
                $statement->execute([
                    'migration' => $name,
                    'batch' => $batch,
                ]);
                $count++;
            } catch (\Throwable $e) {
                throw new RuntimeException("Migration {$name} failed: {$e->getMessage()}", 0, $e);
            }
        }

        return $count;
    }

    private function ensureMigrationTable(string $table): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            throw new RuntimeException('Invalid migration table name.');
        }

        $this->connection->exec(
            "CREATE TABLE IF NOT EXISTS {$this->quoteIdentifier($table)} (\n"
            . "id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n"
            . "migration VARCHAR(255) NOT NULL,\n"
            . "batch INT UNSIGNED NOT NULL,\n"
            . "executed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
            . "UNIQUE KEY uq_migration_name (migration)\n"
            . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function executed(string $table): array
    {
        $rows = $this->connection->query("SELECT migration FROM {$this->quoteIdentifier($table)}")->fetchAll();
        return array_fill_keys(array_column($rows, 'migration'), true);
    }

    private function nextBatch(string $table): int
    {
        $value = $this->connection->query(
            "SELECT COALESCE(MAX(batch), 0) + 1 FROM {$this->quoteIdentifier($table)}"
        )->fetchColumn();

        return max(1, (int) $value);
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . $identifier . '`';
    }
}
