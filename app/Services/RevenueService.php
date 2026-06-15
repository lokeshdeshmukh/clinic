<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RevenueRecord;

final class RevenueService
{
    public function recordCompletedAppointment(array $appointment): void
    {
        $model = new RevenueRecord();
        $model->insert([
            'clinic_id' => $appointment['clinic_id'],
            'doctor_id' => $appointment['doctor_id'],
            'appointment_id' => $appointment['id'],
            'appointment_date' => $appointment['appointment_date'],
            'consultation_fee' => $appointment['consultation_fee'],
            'doctor_revenue' => $appointment['consultation_fee'],
            'clinic_revenue' => $appointment['consultation_fee'],
            'recorded_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
