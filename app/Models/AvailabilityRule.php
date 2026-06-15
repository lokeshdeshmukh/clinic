<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class AvailabilityRule extends Model
{
    protected string $table = 'doctor_availability';

    public function forClinic(int $clinicId): array
    {
        $sql = 'SELECT a.*, d.name AS doctor_name
                FROM doctor_availability a
                INNER JOIN doctors d ON d.id = a.doctor_id
                WHERE a.clinic_id = :clinic_id AND a.deleted_at IS NULL
                ORDER BY COALESCE(a.specific_date, "9999-12-31") ASC, a.weekday ASC, a.start_time ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['clinic_id' => $clinicId]);

        return $statement->fetchAll();
    }

    public function findForClinic(int $id, int $clinicId): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM doctor_availability WHERE id = :id AND clinic_id = :clinic_id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id, 'clinic_id' => $clinicId]);

        return $statement->fetch() ?: null;
    }

    public function forDoctorOnDate(int $doctorId, string $date, int $weekday): array
    {
        $sql = 'SELECT * FROM doctor_availability
                WHERE doctor_id = :doctor_id
                  AND deleted_at IS NULL
                  AND (
                    (rule_type IN ("holiday", "date_override", "blocked_slot") AND specific_date = :specific_date)
                    OR (rule_type = "weekly" AND weekday = :weekday)
                  )
                ORDER BY start_time ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'doctor_id' => $doctorId,
            'specific_date' => $date,
            'weekday' => $weekday,
        ]);

        return $statement->fetchAll();
    }

    public function weeklyForClinic(int $clinicId): array
    {
        $sql = 'SELECT a.*, d.name AS doctor_name
                FROM doctor_availability a
                INNER JOIN doctors d ON d.id = a.doctor_id
                WHERE a.clinic_id = :clinic_id
                  AND a.rule_type = "weekly"
                  AND a.deleted_at IS NULL
                ORDER BY d.name ASC, a.weekday ASC, a.start_time ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['clinic_id' => $clinicId]);

        return $statement->fetchAll();
    }

    public function softDeleteWeeklyForDoctor(int $clinicId, int $doctorId, int $weekday): void
    {
        $sql = 'UPDATE doctor_availability
                SET deleted_at = :deleted_at,
                    updated_at = :updated_at
                WHERE clinic_id = :clinic_id
                  AND doctor_id = :doctor_id
                  AND weekday = :weekday
                  AND rule_type = "weekly"
                  AND deleted_at IS NULL';
        $now = date('Y-m-d H:i:s');
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'clinic_id' => $clinicId,
            'doctor_id' => $doctorId,
            'weekday' => $weekday,
            'deleted_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
