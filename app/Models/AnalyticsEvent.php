<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class AnalyticsEvent extends Model
{
    protected string $table = 'analytics_events';

    public function summaryForClinic(int $clinicId): array
    {
        $sql = 'SELECT
                    SUM(CASE WHEN event_type = "appointment_booked" THEN 1 ELSE 0 END) AS total_bookings,
                    SUM(CASE WHEN event_type = "appointment_booked" AND event_date = CURDATE() THEN 1 ELSE 0 END) AS daily_bookings,
                    SUM(CASE WHEN event_type = "appointment_booked" AND YEAR(event_date) = YEAR(CURDATE()) AND MONTH(event_date) = MONTH(CURDATE()) THEN 1 ELSE 0 END) AS monthly_bookings,
                    SUM(CASE WHEN event_type = "appointment_completed" THEN 1 ELSE 0 END) AS completed_appointments,
                    SUM(CASE WHEN event_type = "appointment_cancelled" THEN 1 ELSE 0 END) AS cancelled_appointments,
                    SUM(CASE WHEN event_type = "patient_registered" AND YEAR(event_date) = YEAR(CURDATE()) AND MONTH(event_date) = MONTH(CURDATE()) THEN 1 ELSE 0 END) AS new_patients
                FROM analytics_events
                WHERE clinic_id = :clinic_id';
        $statement = $this->db->prepare($sql);
        $statement->execute(['clinic_id' => $clinicId]);

        return $statement->fetch() ?: [];
    }

    public function monthlyBookingsSeries(int $clinicId, int $months = 6): array
    {
        $sql = 'SELECT DATE_FORMAT(event_date, "%Y-%m") AS period, COUNT(*) AS total
                FROM analytics_events
                WHERE clinic_id = :clinic_id
                  AND event_type = "appointment_booked"
                  AND event_date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(event_date, "%Y-%m")
                ORDER BY period ASC';
        $statement = $this->db->prepare($sql);
        $statement->bindValue('clinic_id', $clinicId, \PDO::PARAM_INT);
        $statement->bindValue('months', $months, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
