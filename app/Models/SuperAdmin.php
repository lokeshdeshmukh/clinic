<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class SuperAdmin extends Model
{
    protected string $table = 'super_admins';

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM super_admins WHERE email = :email AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['email' => normalize_email($email)]);

        return $statement->fetch() ?: null;
    }

    public function countActive(): int
    {
        $statement = $this->db->query('SELECT COUNT(*) FROM super_admins WHERE deleted_at IS NULL');

        return (int) $statement->fetchColumn();
    }

    public function allActive(): array
    {
        return $this->db->query('SELECT * FROM super_admins WHERE deleted_at IS NULL ORDER BY created_at ASC')->fetchAll();
    }
}
