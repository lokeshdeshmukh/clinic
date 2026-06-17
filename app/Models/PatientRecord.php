<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class PatientRecord extends Model
{
    protected string $table = 'patient_records';

    public function listForClinicPatient(int $clinicId, int $patientId): array
    {
        $sql = 'SELECT pr.*, d.name AS doctor_name, a.appointment_date, a.start_time
                FROM patient_records pr
                LEFT JOIN doctors d ON d.id = pr.doctor_id
                LEFT JOIN appointments a ON a.id = pr.appointment_id
                WHERE pr.clinic_id = :clinic_id
                  AND pr.patient_id = :patient_id
                  AND pr.deleted_at IS NULL
                ORDER BY pr.recorded_at DESC, pr.id DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
        ]);

        return $statement->fetchAll();
    }

    public function findForClinic(int $recordId, int $clinicId): ?array
    {
        $sql = 'SELECT pr.*, d.name AS doctor_name, a.appointment_date, a.start_time
                FROM patient_records pr
                LEFT JOIN doctors d ON d.id = pr.doctor_id
                LEFT JOIN appointments a ON a.id = pr.appointment_id
                WHERE pr.id = :id
                  AND pr.clinic_id = :clinic_id
                  AND pr.deleted_at IS NULL
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'id' => $recordId,
            'clinic_id' => $clinicId,
        ]);

        return $statement->fetch() ?: null;
    }
}
