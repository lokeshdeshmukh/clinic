<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Appointment extends Model
{
    protected string $table = 'appointments';

    public function findDetailed(int $id): ?array
    {
        $sql = 'SELECT a.*, d.name AS doctor_name, d.specialization, d.slot_duration_minutes,
                       p.first_name, p.last_name, p.email AS patient_email, p.phone AS patient_phone,
                       c.name AS clinic_name, c.email AS clinic_email
                FROM appointments a
                INNER JOIN doctors d ON d.id = a.doctor_id
                INNER JOIN patients p ON p.id = a.patient_id
                INNER JOIN clinics c ON c.id = a.clinic_id
                WHERE a.id = :id AND a.deleted_at IS NULL
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }

    public function listForClinic(int $clinicId, string $view = 'upcoming'): array
    {
        $conditions = ['a.clinic_id = :clinic_id', 'a.deleted_at IS NULL'];
        $params = ['clinic_id' => $clinicId];

        if ($view === 'today') {
            $conditions[] = 'a.appointment_date = CURDATE()';
        } elseif ($view === 'completed') {
            $conditions[] = 'a.status = "completed"';
        } else {
            $conditions[] = 'a.appointment_date >= CURDATE()';
            $conditions[] = 'a.status IN ("booked", "confirmed")';
        }

        $sql = 'SELECT a.*, d.name AS doctor_name, p.first_name, p.last_name, p.phone AS patient_phone
                FROM appointments a
                INNER JOIN doctors d ON d.id = a.doctor_id
                INNER JOIN patients p ON p.id = a.patient_id
                WHERE ' . implode(' AND ', $conditions) . '
                ORDER BY a.appointment_date ASC, a.start_time ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function listForPatient(int $patientId): array
    {
        $sql = 'SELECT a.*, d.name AS doctor_name, d.specialization, c.name AS clinic_name, c.phone AS clinic_phone
                FROM appointments a
                INNER JOIN doctors d ON d.id = a.doctor_id
                INNER JOIN clinics c ON c.id = a.clinic_id
                WHERE a.patient_id = :patient_id AND a.deleted_at IS NULL
                ORDER BY a.appointment_date DESC, a.start_time DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['patient_id' => $patientId]);

        return $statement->fetchAll();
    }

    public function listForPatientInClinic(int $patientId, int $clinicId): array
    {
        $sql = 'SELECT a.*, d.name AS doctor_name, d.specialization, c.name AS clinic_name, c.phone AS clinic_phone
                FROM appointments a
                INNER JOIN doctors d ON d.id = a.doctor_id
                INNER JOIN clinics c ON c.id = a.clinic_id
                WHERE a.patient_id = :patient_id
                  AND a.clinic_id = :clinic_id
                  AND a.deleted_at IS NULL
                ORDER BY a.appointment_date DESC, a.start_time DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'patient_id' => $patientId,
            'clinic_id' => $clinicId,
        ]);

        return $statement->fetchAll();
    }

    public function findForPatient(int $id, int $patientId): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM appointments WHERE id = :id AND patient_id = :patient_id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id, 'patient_id' => $patientId]);

        return $statement->fetch() ?: null;
    }

    public function findForClinic(int $id, int $clinicId): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM appointments WHERE id = :id AND clinic_id = :clinic_id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id, 'clinic_id' => $clinicId]);

        return $statement->fetch() ?: null;
    }

    public function activeForDoctorOnDate(int $doctorId, string $date): array
    {
        $sql = 'SELECT * FROM appointments
                WHERE doctor_id = :doctor_id
                  AND appointment_date = :appointment_date
                  AND active_booking = 1
                  AND deleted_at IS NULL';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'doctor_id' => $doctorId,
            'appointment_date' => $date,
        ]);

        return $statement->fetchAll();
    }

    public function calendarEvents(int $clinicId): array
    {
        $sql = 'SELECT a.id, a.appointment_date, a.start_time, a.end_time, a.status, d.name AS doctor_name, p.first_name, p.last_name
                FROM appointments a
                INNER JOIN doctors d ON d.id = a.doctor_id
                INNER JOIN patients p ON p.id = a.patient_id
                WHERE a.clinic_id = :clinic_id
                  AND a.deleted_at IS NULL
                  AND a.appointment_date >= CURDATE() - INTERVAL 30 DAY
                ORDER BY a.appointment_date ASC, a.start_time ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['clinic_id' => $clinicId]);
        $rows = $statement->fetchAll();

        return array_map(static function (array $row): array {
            return [
                'title' => $row['doctor_name'] . ' / ' . trim($row['first_name'] . ' ' . $row['last_name']),
                'start' => $row['appointment_date'] . 'T' . $row['start_time'],
                'end' => $row['appointment_date'] . 'T' . $row['end_time'],
                'color' => match ($row['status']) {
                    'completed' => '#059669',
                    'cancelled' => '#dc2626',
                    default => '#2563eb',
                },
            ];
        }, $rows);
    }
}
