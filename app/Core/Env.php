<?php

declare(strict_types=1);

namespace App\Core;

final class Env
{
    public static function resolvePath(string $basePath): string
    {
        foreach (self::candidatePaths($basePath) as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return $basePath . DIRECTORY_SEPARATOR . '.env';
    }

    public static function candidatePaths(string $basePath): array
    {
        $basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        $parentPath = dirname($basePath);
        $homePath = $_SERVER['HOME'] ?? getenv('HOME') ?: null;
        $serverDefinedPath = $_SERVER['APP_ENV_FILE'] ?? $_ENV['APP_ENV_FILE'] ?? null;

        $candidates = array_filter([
            is_string($serverDefinedPath) && $serverDefinedPath !== '' ? $serverDefinedPath : null,
            is_string($homePath) && $homePath !== '' ? rtrim($homePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.clinicflow.env' : null,
            is_string($homePath) && $homePath !== '' ? rtrim($homePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env' : null,
            $parentPath . DIRECTORY_SEPARATOR . '.clinicflow.env',
            $parentPath . DIRECTORY_SEPARATOR . '.env',
            $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app.env',
            $basePath . DIRECTORY_SEPARATOR . '.env',
        ]);

        return array_values(array_unique($candidates));
    }

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, '\'') && str_ends_with($value, '\''))
            ) {
                $value = substr($value, 1, -1);
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
