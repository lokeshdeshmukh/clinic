<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AnalyticsEvent;

final class AnalyticsService
{
    public function track(string $eventType, array $payload = []): void
    {
        (new AnalyticsEvent())->insert([
            'clinic_id' => $payload['clinic_id'] ?? null,
            'doctor_id' => $payload['doctor_id'] ?? null,
            'patient_id' => $payload['patient_id'] ?? null,
            'appointment_id' => $payload['appointment_id'] ?? null,
            'event_type' => $eventType,
            'event_date' => $payload['event_date'] ?? date('Y-m-d'),
            'metadata_json' => isset($payload['metadata']) ? json_encode($payload['metadata'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
