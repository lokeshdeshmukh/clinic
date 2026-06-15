<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\SuperAdmin;

final class Auth
{
    private const SESSION_GUARD = 'auth_guard';
    private const SESSION_ID = 'auth_id';

    public static function login(string $guard, int $id): void
    {
        Session::regenerate();
        Session::put(self::SESSION_GUARD, $guard);
        Session::put(self::SESSION_ID, $id);
    }

    public static function logout(): void
    {
        Session::forget(self::SESSION_GUARD);
        Session::forget(self::SESSION_ID);
        Session::regenerate();
    }

    public static function check(?string $guard = null): bool
    {
        $current = Session::get(self::SESSION_GUARD);
        $id = Session::get(self::SESSION_ID);

        if (!$current || !$id) {
            return false;
        }

        return $guard === null ? true : $current === $guard;
    }

    public static function guard(): ?string
    {
        return Session::get(self::SESSION_GUARD);
    }

    public static function id(): ?int
    {
        $id = Session::get(self::SESSION_ID);

        return is_numeric($id) ? (int) $id : null;
    }

    public static function user(): ?array
    {
        $guard = self::guard();
        $id = self::id();

        if ($guard === null || $id === null) {
            return null;
        }

        return match ($guard) {
            'clinic' => (new Clinic())->findActiveById($id),
            'patient' => (new Patient())->findActiveById($id),
            'super_admin' => (new SuperAdmin())->findActiveById($id),
            default => null,
        };
    }
}
