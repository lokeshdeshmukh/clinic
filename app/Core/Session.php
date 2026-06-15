<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    private const FLASH_KEY = '_flash';
    private const FLASH_OLD_INPUT = '_old_input';

    public static function start(string $name): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $domain = trim((string) config('app.session_domain', ''));
        $cookieParams = [
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ];
        if ($domain !== '') {
            $cookieParams['domain'] = $domain;
        }

        session_name($name);
        session_set_cookie_params($cookieParams);
        session_start();

        $_SESSION[self::FLASH_KEY] ??= [];
        $_SESSION[self::FLASH_OLD_INPUT] ??= [];
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function flash(string $key, string $message): void
    {
        $_SESSION[self::FLASH_KEY][$key] = $message;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION[self::FLASH_KEY][$key] ?? $default;
        unset($_SESSION[self::FLASH_KEY][$key]);

        return $value;
    }

    public static function flashInput(array $input): void
    {
        unset($input['_token'], $input['password'], $input['password_confirmation'], $input['otp']);
        $_SESSION[self::FLASH_OLD_INPUT] = $input;
    }

    public static function getFlashInput(string $key, mixed $default = ''): mixed
    {
        $value = $_SESSION[self::FLASH_OLD_INPUT][$key] ?? $default;
        unset($_SESSION[self::FLASH_OLD_INPUT][$key]);

        return $value;
    }
}
