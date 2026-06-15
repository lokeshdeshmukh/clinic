<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;

final class MigrationService
{
    public function runPending(): array
    {
        $pdo = Database::connection();
        $this->ensureTrackingTable($pdo);

        return [
            'migrations' => $this->runDirectory($pdo, base_path('database/migrations'), 'migration'),
            'seeds' => $this->runDirectory($pdo, base_path('database/seeds'), 'seed'),
        ];
    }

    private function ensureTrackingTable(PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration_key VARCHAR(190) NOT NULL UNIQUE,
            migration_type ENUM("migration", "seed") NOT NULL,
            applied_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    private function runDirectory(PDO $pdo, string $directory, string $type): array
    {
        $files = glob($directory . '/*.sql') ?: [];
        sort($files);

        $applied = $this->appliedKeys($pdo);
        $executed = [];

        foreach ($files as $file) {
            $key = $type . ':' . basename($file);
            if (in_array($key, $applied, true)) {
                continue;
            }

            $sql = file_get_contents($file);
            if ($sql === false) {
                throw new RuntimeException('Unable to read SQL file: ' . basename($file));
            }

            $pdo->exec($sql);
            $statement = $pdo->prepare('INSERT INTO schema_migrations (migration_key, migration_type, applied_at) VALUES (:migration_key, :migration_type, :applied_at)');
            $statement->execute([
                'migration_key' => $key,
                'migration_type' => $type,
                'applied_at' => date('Y-m-d H:i:s'),
            ]);

            $executed[] = basename($file);
        }

        return $executed;
    }

    private function appliedKeys(PDO $pdo): array
    {
        $statement = $pdo->query('SELECT migration_key FROM schema_migrations');
        return $statement->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
}
