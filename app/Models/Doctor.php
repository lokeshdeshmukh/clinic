<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Doctor extends Model
{
    protected string $table = 'doctors';

    public function forClinic(int $clinicId): array
    {
        $statement = $this->db->prepare('SELECT * FROM doctors WHERE clinic_id = :clinic_id AND deleted_at IS NULL ORDER BY name ASC');
        $statement->execute(['clinic_id' => $clinicId]);

        return $statement->fetchAll();
    }

    public function findByIdForClinic(int $id, int $clinicId): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM doctors WHERE id = :id AND clinic_id = :clinic_id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id, 'clinic_id' => $clinicId]);

        return $statement->fetch() ?: null;
    }

    public function publicFind(int $id): ?array
    {
        $sql = 'SELECT d.*, c.name AS clinic_name, c.slug AS clinic_slug, c.address AS clinic_address, c.phone AS clinic_phone,
                       c.email AS clinic_email, c.logo_path AS clinic_logo_path
                FROM doctors d
                INNER JOIN clinics c ON c.id = d.clinic_id
                WHERE d.id = :id AND d.deleted_at IS NULL AND d.status = "active" AND c.deleted_at IS NULL AND c.status = "active"
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function forClinicSlug(string $slug): array
    {
        $sql = 'SELECT d.*, c.name AS clinic_name
                FROM doctors d
                INNER JOIN clinics c ON c.id = d.clinic_id
                WHERE c.slug = :slug AND c.status = "active" AND c.deleted_at IS NULL
                  AND d.deleted_at IS NULL AND d.status = "active"
                ORDER BY d.name ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['slug' => $slug]);

        return $statement->fetchAll();
    }
}
