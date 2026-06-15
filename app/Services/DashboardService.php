<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\RevenueRecord;

final class DashboardService
{
    public function dataForClinic(int $clinicId): array
    {
        $doctorCount = count((new Doctor())->forClinic($clinicId));
        $analyticsSummary = (new AnalyticsEvent())->summaryForClinic($clinicId);
        $revenueSummary = (new RevenueRecord())->summaryForClinic($clinicId);
        $appointmentModel = new Appointment();
        $calendarEvents = $appointmentModel->calendarEvents($clinicId);
        $upcomingAppointments = array_slice($appointmentModel->listForClinic($clinicId, 'upcoming'), 0, 8);
        $todayAppointments = $appointmentModel->listForClinic($clinicId, 'today');

        $pdo = \App\Core\Database::connection();

        $patientCountStatement = $pdo->prepare('SELECT COUNT(DISTINCT patient_id) FROM appointments WHERE clinic_id = :clinic_id AND deleted_at IS NULL');
        $patientCountStatement->execute(['clinic_id' => $clinicId]);
        $patientCount = (int) $patientCountStatement->fetchColumn();

        $topDoctorsStatement = $pdo->prepare('SELECT d.name, COUNT(a.id) AS total_appointments
            FROM doctors d
            LEFT JOIN appointments a ON a.doctor_id = d.id AND a.deleted_at IS NULL
            WHERE d.clinic_id = :clinic_id AND d.deleted_at IS NULL
            GROUP BY d.id
            ORDER BY total_appointments DESC, d.name ASC
            LIMIT 5');
        $topDoctorsStatement->execute(['clinic_id' => $clinicId]);

        $utilizationStatement = $pdo->prepare('SELECT d.name,
            SUM(CASE WHEN a.status = "completed" THEN 1 ELSE 0 END) AS completed_count,
            COUNT(a.id) AS total_count
            FROM doctors d
            LEFT JOIN appointments a ON a.doctor_id = d.id AND a.deleted_at IS NULL
            WHERE d.clinic_id = :clinic_id AND d.deleted_at IS NULL
            GROUP BY d.id
            ORDER BY total_count DESC');
        $utilizationStatement->execute(['clinic_id' => $clinicId]);

        $returningPatientsStatement = $pdo->prepare('SELECT COUNT(*) FROM (
            SELECT patient_id, COUNT(*) AS total_visits
            FROM appointments
            WHERE clinic_id = :clinic_id AND deleted_at IS NULL
            GROUP BY patient_id
            HAVING total_visits > 1
        ) AS returning_patients');
        $returningPatientsStatement->execute(['clinic_id' => $clinicId]);

        $newPatientsStatement = $pdo->prepare('SELECT COUNT(*) FROM (
            SELECT patient_id, MIN(appointment_date) AS first_visit
            FROM appointments
            WHERE clinic_id = :clinic_id AND deleted_at IS NULL
            GROUP BY patient_id
            HAVING YEAR(first_visit) = YEAR(CURDATE()) AND MONTH(first_visit) = MONTH(CURDATE())
        ) AS new_patients');
        $newPatientsStatement->execute(['clinic_id' => $clinicId]);

        $bookingSeries = (new AnalyticsEvent())->monthlyBookingsSeries($clinicId);
        $revenueSeries = (new RevenueRecord())->monthlySeries($clinicId);

        return [
            'metrics' => [
                'today_appointments' => count($todayAppointments),
                'total_doctors' => $doctorCount,
                'total_patients' => $patientCount,
                'monthly_appointments' => (int) ($analyticsSummary['monthly_bookings'] ?? 0),
                'monthly_revenue' => (float) ($revenueSummary['monthly_revenue'] ?? 0),
                'daily_revenue' => (float) ($revenueSummary['daily_revenue'] ?? 0),
                'yearly_revenue' => (float) ($revenueSummary['yearly_revenue'] ?? 0),
                'completed_appointments' => (int) ($analyticsSummary['completed_appointments'] ?? 0),
                'cancelled_appointments' => (int) ($analyticsSummary['cancelled_appointments'] ?? 0),
                'new_patients' => (int) $newPatientsStatement->fetchColumn(),
                'returning_patients' => (int) $returningPatientsStatement->fetchColumn(),
            ],
            'calendar_events' => $calendarEvents,
            'upcoming_appointments' => $upcomingAppointments,
            'top_doctors' => $topDoctorsStatement->fetchAll(),
            'doctor_utilization' => $utilizationStatement->fetchAll(),
            'booking_series' => $bookingSeries,
            'revenue_series' => $revenueSeries,
        ];
    }
}
