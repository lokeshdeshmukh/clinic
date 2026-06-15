<?php

declare(strict_types=1);

namespace App\Core;

final class BuildInfo
{
    public static function resolve(string $basePath): array
    {
        $meta = [
            'version' => '0.0.0.1',
            'commit' => 'manual',
            'deployed_at' => null,
            'source' => 'zip-upload',
        ];

        $storageMetaFile = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app_version.json';
        if (is_file($storageMetaFile)) {
            $decoded = json_decode((string) file_get_contents($storageMetaFile), true);
            if (is_array($decoded)) {
                $meta = array_merge($meta, $decoded);
            }
        }

        $gitMeta = self::gitMetadata($basePath);
        if ($gitMeta !== []) {
            $meta = array_merge($meta, $gitMeta);
        }

        return $meta;
    }

    private static function gitMetadata(string $basePath): array
    {
        $gitDir = self::resolveGitDir($basePath);
        if ($gitDir === null) {
            return [];
        }

        $headFile = $gitDir . DIRECTORY_SEPARATOR . 'HEAD';
        if (!is_file($headFile)) {
            return [];
        }

        $head = trim((string) file_get_contents($headFile));
        if ($head === '') {
            return [];
        }

        $hash = '';
        $refPath = null;

        if (str_starts_with($head, 'ref: ')) {
            $ref = trim(substr($head, 5));
            $refPath = $gitDir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $ref);
            if (is_file($refPath)) {
                $hash = trim((string) file_get_contents($refPath));
            } else {
                $hash = self::hashFromPackedRefs($gitDir, $ref);
            }
        } else {
            $hash = $head;
        }

        if ($hash === '') {
            return [];
        }

        $count = self::commitCount($gitDir);
        $timestampSource = $refPath && is_file($refPath) ? $refPath : $headFile;

        return [
            'version' => '0.0.0.' . max(1, $count),
            'commit' => substr($hash, 0, 7),
            'deployed_at' => date('Y-m-d H:i:s T', filemtime($timestampSource) ?: time()),
            'source' => 'git',
        ];
    }

    private static function resolveGitDir(string $basePath): ?string
    {
        $gitPath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.git';
        if (is_dir($gitPath)) {
            return $gitPath;
        }

        if (is_file($gitPath)) {
            $contents = trim((string) file_get_contents($gitPath));
            if (preg_match('/^gitdir:\s*(.+)$/i', $contents, $matches) === 1) {
                $gitDir = $matches[1];
                if (!str_starts_with($gitDir, DIRECTORY_SEPARATOR)) {
                    $gitDir = dirname($gitPath) . DIRECTORY_SEPARATOR . $gitDir;
                }

                return is_dir($gitDir) ? realpath($gitDir) ?: $gitDir : null;
            }
        }

        return null;
    }

    private static function hashFromPackedRefs(string $gitDir, string $ref): string
    {
        $packedRefs = $gitDir . DIRECTORY_SEPARATOR . 'packed-refs';
        if (!is_file($packedRefs)) {
            return '';
        }

        $lines = file($packedRefs, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            if (str_starts_with($line, '#') || str_starts_with($line, '^')) {
                continue;
            }

            [$hash, $packedRef] = array_pad(preg_split('/\s+/', trim($line), 2) ?: [], 2, '');
            if ($packedRef === $ref) {
                return $hash;
            }
        }

        return '';
    }

    private static function commitCount(string $gitDir): int
    {
        $logsHead = $gitDir . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'HEAD';
        if (!is_file($logsHead)) {
            return 1;
        }

        $lines = file($logsHead, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        return max(1, count($lines));
    }
}
