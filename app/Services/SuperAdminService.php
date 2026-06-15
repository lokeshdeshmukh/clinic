<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Models\SuperAdmin;
use RuntimeException;

final class SuperAdminService
{
    public function __construct(private readonly SuperAdmin $admins = new SuperAdmin())
    {
    }

    public function hasAnyAdmin(): bool
    {
        return $this->admins->countActive() > 0;
    }

    public function createFirstAdmin(array $data): array
    {
        if ($this->hasAnyAdmin()) {
            throw new RuntimeException('Platform admin setup has already been completed.');
        }

        $email = normalize_email((string) ($data['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Enter a valid platform admin email address.');
        }

        if (strlen((string) ($data['password'] ?? '')) < 8) {
            throw new RuntimeException('Platform admin password must be at least 8 characters.');
        }

        $now = date('Y-m-d H:i:s');
        $adminId = $this->admins->insert([
            'name' => trim((string) ($data['name'] ?? 'Platform Admin')),
            'email' => $email,
            'password_hash' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
            'status' => 'active',
            'last_login_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);

        return $this->admins->findActiveById($adminId) ?? [];
    }

    public function attemptLogin(string $email, string $password): bool
    {
        $admin = $this->admins->findByEmail($email);
        if (!$admin || ($admin['status'] ?? '') !== 'active' || !password_verify($password, (string) $admin['password_hash'])) {
            return false;
        }

        Auth::login('super_admin', (int) $admin['id']);
        $this->admins->updateById((int) $admin['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }
}
