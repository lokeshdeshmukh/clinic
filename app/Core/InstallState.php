<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

final class InstallState
{
    private const REQUIRED_TABLES = [
        'clinics',
        'doctors',
        'doctor_availability',
        'patients',
        'appointments',
    ];

    public static function lockPath(string $basePath): string
    {
        return rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'installed.lock';
    }

    public static function isInstalled(string $basePath): bool
    {
        $envPath = Env::resolvePath($basePath);
        if (!is_file($envPath)) {
            return false;
        }

        Env::load($envPath);

        $host = (string) ($_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '');
        $port = (string) ($_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? '3306');
        $database = (string) ($_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? '');
        $username = (string) ($_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? '');
        $password = (string) ($_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? '');
        $charset = (string) ($_ENV['DB_CHARSET'] ?? $_SERVER['DB_CHARSET'] ?? 'utf8mb4');

        if ($host === '' || $database === '' || $username === '') {
            return false;
        }

        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $database, $charset),
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (Throwable) {
            return false;
        }

        if (self::tableExists($pdo, 'schema_migrations')) {
            return true;
        }

        foreach (self::REQUIRED_TABLES as $table) {
            if (!self::tableExists($pdo, $table)) {
                return false;
            }
        }

        return true;
    }

    public static function ensureLockFile(string $basePath): bool
    {
        $lockPath = self::lockPath($basePath);
        $directory = dirname($lockPath);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return false;
        }

        if (is_file($lockPath)) {
            return true;
        }

        return file_put_contents($lockPath, 'Installed at ' . date('c') . ' (auto-restored)') !== false;
    }

    private static function tableExists(PDO $pdo, string $table): bool
    {
        $statement = $pdo->prepare('SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table_name LIMIT 1');
        $statement->execute([
            'table_name' => $table,
        ]);

        return $statement->fetchColumn() !== false;
    }
}
