<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        $token = Session::get(self::SESSION_KEY);

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::put(self::SESSION_KEY, $token);
        }

        return $token;
    }

    public static function validate(?string $token): bool
    {
        $stored = Session::get(self::SESSION_KEY, '');

        return is_string($token) && is_string($stored) && hash_equals($stored, $token);
    }
}
