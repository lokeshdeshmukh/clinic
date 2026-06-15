<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Patient extends Model
{
    protected string $table = 'patients';

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM patients WHERE email = :email AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['email' => $email]);

        return $statement->fetch() ?: null;
    }

    public function findByResetToken(string $token): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM patients WHERE reset_token = :token AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['token' => $token]);

        return $statement->fetch() ?: null;
    }

    public function fullName(array $patient): string
    {
        return trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''));
    }
}
