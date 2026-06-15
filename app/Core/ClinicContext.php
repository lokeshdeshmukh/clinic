<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Clinic;

final class ClinicContext
{
    private static bool $resolved = false;
    private static ?array $clinic = null;

    public static function current(): ?array
    {
        if (!self::$resolved) {
            self::resolve();
        }

        return self::$clinic;
    }

    public static function isScoped(): bool
    {
        return self::current() !== null;
    }

    private static function resolve(): void
    {
        self::$resolved = true;

        $slug = self::slugFromCurrentHost();
        if ($slug === null) {
            return;
        }

        $clinic = (new Clinic())->findPublicBySlug($slug);
        if ($clinic && ($clinic['status'] ?? '') === 'active') {
            self::$clinic = $clinic;
        }
    }

    private static function slugFromCurrentHost(): ?string
    {
        $host = self::normalizeHost((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
        if ($host === '' || $host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        $configuredHost = self::normalizeHost((string) (parse_url((string) config('app.url', ''), PHP_URL_HOST) ?: ''));
        if ($configuredHost !== '' && $host === $configuredHost) {
            return null;
        }

        $segments = explode('.', $host);
        if (count($segments) < 3) {
            return null;
        }

        $candidate = trim((string) ($segments[0] ?? ''));
        if ($candidate === '' || $candidate === 'www') {
            return null;
        }

        $candidate = trim((string) preg_replace('/[^a-z0-9-]+/', '-', strtolower($candidate)), '-');

        return $candidate !== '' ? $candidate : null;
    }

    private static function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        $host = preg_replace('/:\d+$/', '', $host) ?? $host;

        return preg_replace('/^www\./', '', $host) ?? $host;
    }
}
