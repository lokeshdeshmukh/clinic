<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Clinic extends Model
{
    protected string $table = 'clinics';

    public function findByEmail(string $email): ?array
    {
        $email = normalize_email($email);
        $statement = $this->db->prepare('SELECT * FROM clinics WHERE email = :email AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['email' => $email]);

        return $statement->fetch() ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM clinics WHERE slug = :slug AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['slug' => $slug]);

        return $statement->fetch() ?: null;
    }

    public function findPublicBySlug(string $slug): ?array
    {
        $sql = 'SELECT c.id, c.name, c.slug, c.address, c.phone, c.email, c.logo_path, c.status,
                       COUNT(d.id) AS doctor_count
                FROM clinics c
                LEFT JOIN doctors d ON d.clinic_id = c.id AND d.deleted_at IS NULL AND d.status = "active"
                WHERE c.slug = :slug
                  AND c.deleted_at IS NULL
                  AND c.status = "active"
                GROUP BY c.id
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute(['slug' => $slug]);

        return $statement->fetch() ?: null;
    }

    public function findByVerificationToken(string $token): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM clinics WHERE verification_token = :token AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['token' => $token]);

        return $statement->fetch() ?: null;
    }

    public function findByResetToken(string $token): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM clinics WHERE reset_token = :token AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['token' => $token]);

        return $statement->fetch() ?: null;
    }

    public function slugExists(string $slug): bool
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM clinics WHERE slug = :slug');
        $statement->execute(['slug' => $slug]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function publicDirectory(): array
    {
        $sql = 'SELECT c.*, COUNT(d.id) AS doctor_count
                FROM clinics c
                LEFT JOIN doctors d ON d.clinic_id = c.id AND d.deleted_at IS NULL AND d.status = "active"
                WHERE c.deleted_at IS NULL AND c.status = "active"
                GROUP BY c.id
                ORDER BY c.name ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function allForPlatform(): array
    {
        $sql = 'SELECT c.*, COUNT(d.id) AS doctor_count
                FROM clinics c
                LEFT JOIN doctors d ON d.clinic_id = c.id AND d.deleted_at IS NULL
                WHERE c.deleted_at IS NULL
                GROUP BY c.id
                ORDER BY c.created_at DESC';

        return $this->db->query($sql)->fetchAll();
    }
}
