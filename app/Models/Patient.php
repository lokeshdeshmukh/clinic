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

    public function forClinic(int $clinicId): array
    {
        $sql = 'SELECT p.*,
                       stats.total_appointments,
                       stats.completed_visits,
                       stats.last_visit_date,
                       COALESCE(records.total_records, 0) AS total_records
                FROM patients p
                INNER JOIN (
                    SELECT patient_id,
                           COUNT(*) AS total_appointments,
                           SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) AS completed_visits,
                           MAX(appointment_date) AS last_visit_date
                    FROM appointments
                    WHERE clinic_id = :clinic_id_stats
                      AND deleted_at IS NULL
                    GROUP BY patient_id
                ) AS stats ON stats.patient_id = p.id
                LEFT JOIN (
                    SELECT patient_id,
                           COUNT(*) AS total_records
                    FROM patient_records
                    WHERE clinic_id = :clinic_id_records
                      AND deleted_at IS NULL
                    GROUP BY patient_id
                ) AS records ON records.patient_id = p.id
                WHERE p.deleted_at IS NULL
                ORDER BY stats.last_visit_date DESC, p.first_name ASC, p.last_name ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'clinic_id_stats' => $clinicId,
            'clinic_id_records' => $clinicId,
        ]);

        return $statement->fetchAll();
    }

    public function findForClinic(int $patientId, int $clinicId): ?array
    {
        $sql = 'SELECT p.*,
                       stats.total_appointments,
                       stats.completed_visits,
                       stats.last_visit_date,
                       COALESCE(records.total_records, 0) AS total_records
                FROM patients p
                INNER JOIN (
                    SELECT patient_id,
                           COUNT(*) AS total_appointments,
                           SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) AS completed_visits,
                           MAX(appointment_date) AS last_visit_date
                    FROM appointments
                    WHERE clinic_id = :clinic_id_stats
                      AND deleted_at IS NULL
                    GROUP BY patient_id
                ) AS stats ON stats.patient_id = p.id
                LEFT JOIN (
                    SELECT patient_id,
                           COUNT(*) AS total_records
                    FROM patient_records
                    WHERE clinic_id = :clinic_id_records
                      AND deleted_at IS NULL
                    GROUP BY patient_id
                ) AS records ON records.patient_id = p.id
                WHERE p.id = :patient_id
                  AND p.deleted_at IS NULL
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'clinic_id_stats' => $clinicId,
            'clinic_id_records' => $clinicId,
            'patient_id' => $patientId,
        ]);

        return $statement->fetch() ?: null;
    }

    public function touchLogin(int $patientId): void
    {
        $this->updateById($patientId, [
            'last_login_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
