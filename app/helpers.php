<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Session;

function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}

function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}

function base_path(string $path = ''): string
{
    $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__);

    return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $base;
}

function storage_path(string $path = ''): string
{
    $base = base_path('storage');

    return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $base;
}

function public_path(string $path = ''): string
{
    $base = base_path('public');

    return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $base;
}

function view_path(string $path = ''): string
{
    $base = base_path('resources/views');

    return $path ? $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $base;
}

function url(string $path = ''): string
{
    $root = rtrim((string) config('app.url', ''), '/');
    $path = ltrim($path, '/');

    return $path === '' ? $root : $root . '/' . $path;
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function e(mixed $value): string
{
    if ($value === null) {
        return '';
    }

    if ($value instanceof Stringable || is_scalar($value)) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    return htmlspecialchars($encoded === false ? '' : $encoded, ENT_QUOTES, 'UTF-8');
}

function old(string $key, mixed $default = ''): mixed
{
    return Session::getFlashInput($key, $default);
}

function flash(string $key, string $message): void
{
    Session::flash($key, $message);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function selected(mixed $left, mixed $right): string
{
    return (string) $left === (string) $right ? 'selected' : '';
}

function checked(mixed $left, mixed $right): string
{
    return (string) $left === (string) $right ? 'checked' : '';
}
