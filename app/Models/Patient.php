<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Patient extends Model
{
    protected string $table = 'patients';

    public function findByEmail(string $email): ?array
    {
        $email = normalize_email($email);
        $statement = $this->db->prepare('SELECT * FROM patients WHERE email = :email AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['email' => $email]);

        return $statement->fetch() ?: null;
    }

    public function findByPhone(string $phone): ?array
    {
        $normalized = normalize_phone($phone);
        if ($normalized === '') {
            return null;
        }

        $sql = 'SELECT * FROM patients
                WHERE deleted_at IS NULL
                  AND phone IS NOT NULL
                  AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, " ", ""), "-", ""), "(", ""), ")", ""), "+", "") = :phone
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute(['phone' => $normalized]);

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

    public function touchLogin(int $patientId): void
    {
        $this->updateById($patientId, [
            'last_login_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
