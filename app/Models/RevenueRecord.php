<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class RevenueRecord extends Model
{
    protected string $table = 'revenue_records';

    public function summaryForClinic(int $clinicId): array
    {
        $sql = 'SELECT
                    COALESCE(SUM(CASE WHEN appointment_date = CURDATE() THEN clinic_revenue ELSE 0 END), 0) AS daily_revenue,
                    COALESCE(SUM(CASE WHEN YEAR(appointment_date) = YEAR(CURDATE()) AND MONTH(appointment_date) = MONTH(CURDATE()) THEN clinic_revenue ELSE 0 END), 0) AS monthly_revenue,
                    COALESCE(SUM(CASE WHEN YEAR(appointment_date) = YEAR(CURDATE()) THEN clinic_revenue ELSE 0 END), 0) AS yearly_revenue
                FROM revenue_records
                WHERE clinic_id = :clinic_id';
        $statement = $this->db->prepare($sql);
        $statement->execute(['clinic_id' => $clinicId]);

        return $statement->fetch() ?: [];
    }

    public function monthlySeries(int $clinicId, int $months = 6): array
    {
        $sql = 'SELECT DATE_FORMAT(appointment_date, "%Y-%m") AS period, SUM(clinic_revenue) AS total
                FROM revenue_records
                WHERE clinic_id = :clinic_id
                  AND appointment_date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(appointment_date, "%Y-%m")
                ORDER BY period ASC';
        $statement = $this->db->prepare($sql);
        $statement->bindValue('clinic_id', $clinicId, \PDO::PARAM_INT);
        $statement->bindValue('months', $months, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
