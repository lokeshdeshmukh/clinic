<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

final class ReportService
{
    public function appointmentReport(int $clinicId): array
    {
        $sql = 'SELECT a.id, a.appointment_date, a.start_time, a.end_time, a.status,
                       d.name AS doctor_name, p.first_name, p.last_name, a.consultation_fee
                FROM appointments a
                INNER JOIN doctors d ON d.id = a.doctor_id
                INNER JOIN patients p ON p.id = a.patient_id
                WHERE a.clinic_id = :clinic_id AND a.deleted_at IS NULL
                ORDER BY a.appointment_date DESC, a.start_time DESC';
        $statement = Database::connection()->prepare($sql);
        $statement->execute(['clinic_id' => $clinicId]);
        $rows = $statement->fetchAll();

        return array_map(static function (array $row): array {
            return [
                'Appointment ID' => $row['id'],
                'Date' => $row['appointment_date'],
                'Start Time' => $row['start_time'],
                'End Time' => $row['end_time'],
                'Status' => $row['status'],
                'Doctor' => $row['doctor_name'],
                'Patient' => trim($row['first_name'] . ' ' . $row['last_name']),
                'Consultation Fee' => $row['consultation_fee'],
            ];
        }, $rows);
    }

    public function revenueReport(int $clinicId): array
    {
        $sql = 'SELECT rr.appointment_date, d.name AS doctor_name, rr.consultation_fee, rr.doctor_revenue, rr.clinic_revenue
                FROM revenue_records rr
                INNER JOIN doctors d ON d.id = rr.doctor_id
                WHERE rr.clinic_id = :clinic_id
                ORDER BY rr.appointment_date DESC';
        $statement = Database::connection()->prepare($sql);
        $statement->execute(['clinic_id' => $clinicId]);
        $rows = $statement->fetchAll();

        return array_map(static function (array $row): array {
            return [
                'Date' => $row['appointment_date'],
                'Doctor' => $row['doctor_name'],
                'Consultation Fee' => $row['consultation_fee'],
                'Doctor Revenue' => $row['doctor_revenue'],
                'Clinic Revenue' => $row['clinic_revenue'],
            ];
        }, $rows);
    }

    public function doctorPerformanceReport(int $clinicId): array
    {
        $sql = 'SELECT d.name, d.specialization,
                       COUNT(a.id) AS appointments_count,
                       SUM(CASE WHEN a.status = "completed" THEN 1 ELSE 0 END) AS completed_count,
                       SUM(CASE WHEN a.status = "cancelled" THEN 1 ELSE 0 END) AS cancelled_count,
                       COALESCE(SUM(CASE WHEN a.status = "completed" THEN a.consultation_fee ELSE 0 END), 0) AS generated_revenue
                FROM doctors d
                LEFT JOIN appointments a ON a.doctor_id = d.id AND a.deleted_at IS NULL
                WHERE d.clinic_id = :clinic_id AND d.deleted_at IS NULL
                GROUP BY d.id
                ORDER BY appointments_count DESC';
        $statement = Database::connection()->prepare($sql);
        $statement->execute(['clinic_id' => $clinicId]);

        return array_map(static function (array $row): array {
            return [
                'Doctor' => $row['name'],
                'Specialization' => $row['specialization'],
                'Appointments' => $row['appointments_count'],
                'Completed' => $row['completed_count'],
                'Cancelled' => $row['cancelled_count'],
                'Generated Revenue' => $row['generated_revenue'],
            ];
        }, $statement->fetchAll());
    }
}
