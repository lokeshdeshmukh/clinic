<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Appointment;
use App\Models\AvailabilityRule;
use App\Models\Doctor;
use RuntimeException;

final class AvailabilityService
{
    public function __construct(
        private readonly AvailabilityRule $rules = new AvailabilityRule(),
        private readonly Doctor $doctors = new Doctor(),
        private readonly Appointment $appointments = new Appointment()
    ) {
    }

    public function createRule(int $clinicId, array $data): void
    {
        $doctor = $this->doctors->findByIdForClinic((int) $data['doctor_id'], $clinicId);
        if (!$doctor) {
            throw new RuntimeException('Doctor not found.');
        }

        $ruleType = (string) $data['rule_type'];
        $specificDate = $data['specific_date'] ?: null;
        $weekday = $data['weekday'] !== '' ? (int) $data['weekday'] : null;
        $startTime = $data['start_time'] ?: null;
        $endTime = $data['end_time'] ?: null;

        if ($ruleType === 'weekly' && $weekday === null) {
            throw new RuntimeException('Weekday is required for weekly schedules.');
        }

        if (in_array($ruleType, ['date_override', 'holiday', 'blocked_slot'], true) && !$specificDate) {
            throw new RuntimeException('A date is required for this availability rule.');
        }

        if (in_array($ruleType, ['weekly', 'date_override', 'blocked_slot'], true) && (!$startTime || !$endTime)) {
            throw new RuntimeException('Start and end times are required for time-based rules.');
        }

        if ($startTime && $endTime && strtotime($startTime) >= strtotime($endTime)) {
            throw new RuntimeException('End time must be later than start time.');
        }

        if ($ruleType === 'weekly' && $weekday !== null) {
            $this->rules->softDeleteWeeklyForDoctor($clinicId, (int) $data['doctor_id'], $weekday);
        }

        $now = date('Y-m-d H:i:s');
        $this->rules->insert([
            'clinic_id' => $clinicId,
            'doctor_id' => (int) $data['doctor_id'],
            'rule_type' => $ruleType,
            'weekday' => $weekday,
            'specific_date' => $specificDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_available' => $ruleType === 'blocked_slot' || $ruleType === 'holiday' ? 0 : 1,
            'slot_interval_minutes' => $data['slot_interval_minutes'] !== '' ? (int) $data['slot_interval_minutes'] : null,
            'reason' => trim((string) ($data['reason'] ?? '')) ?: null,
            'created_by_type' => 'clinic',
            'created_by_id' => $clinicId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);
    }

    public function saveWeeklyRule(int $clinicId, array $data): void
    {
        $this->createRule($clinicId, [
            'doctor_id' => (int) ($data['doctor_id'] ?? 0),
            'rule_type' => 'weekly',
            'weekday' => (string) ($data['weekday'] ?? ''),
            'specific_date' => null,
            'start_time' => (string) ($data['start_time'] ?? ''),
            'end_time' => (string) ($data['end_time'] ?? ''),
            'slot_interval_minutes' => (string) ($data['slot_interval_minutes'] ?? ''),
            'reason' => trim((string) ($data['reason'] ?? 'Weekly clinic schedule')),
        ]);
    }

    public function deleteRule(int $clinicId, int $ruleId): void
    {
        $rule = $this->rules->findForClinic($ruleId, $clinicId);
        if (!$rule) {
            throw new RuntimeException('Availability rule not found.');
        }

        $this->rules->softDelete($ruleId);
    }

    public function getAvailableSlots(int $doctorId, string $date): array
    {
        $doctor = $this->doctors->findActiveById($doctorId);
        if (!$doctor || ($doctor['status'] ?? '') !== 'active') {
            return [];
        }

        if (strtotime($date) === false || $date < date('Y-m-d')) {
            return [];
        }

        $weekday = (int) date('w', strtotime($date));
        $rules = $this->rules->forDoctorOnDate($doctorId, $date, $weekday);

        $holidays = array_filter($rules, static fn (array $rule): bool => $rule['rule_type'] === 'holiday');
        if ($holidays !== []) {
            return [];
        }

        $overrideRules = array_values(array_filter($rules, static fn (array $rule): bool => $rule['rule_type'] === 'date_override' && (int) $rule['is_available'] === 1));
        $weeklyRules = array_values(array_filter($rules, static fn (array $rule): bool => $rule['rule_type'] === 'weekly' && (int) $rule['is_available'] === 1));
        $blockedRules = array_values(array_filter($rules, static fn (array $rule): bool => $rule['rule_type'] === 'blocked_slot'));
        $activeRules = $overrideRules !== [] ? $overrideRules : $weeklyRules;

        $slots = [];
        foreach ($activeRules as $rule) {
            $interval = (int) ($rule['slot_interval_minutes'] ?: $doctor['slot_duration_minutes'] ?: config('app.default_slot_duration', 30));
            $slots = array_merge($slots, $this->generateSlots((string) $rule['start_time'], (string) $rule['end_time'], $interval));
        }

        $blocked = array_map(static fn (array $rule): array => [
            'start_time' => $rule['start_time'],
            'end_time' => $rule['end_time'],
        ], $blockedRules);
        $appointments = $this->appointments->activeForDoctorOnDate($doctorId, $date);

        return array_values(array_filter(array_map(function (array $slot) use ($blocked, $appointments): ?array {
            foreach ($blocked as $range) {
                if ($this->overlaps($slot['start_time'], $slot['end_time'], (string) $range['start_time'], (string) $range['end_time'])) {
                    return null;
                }
            }

            foreach ($appointments as $appointment) {
                if ($this->overlaps($slot['start_time'], $slot['end_time'], (string) $appointment['start_time'], (string) $appointment['end_time'])) {
                    return null;
                }
            }

            return [
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'label' => date('g:i A', strtotime($slot['start_time'])) . ' - ' . date('g:i A', strtotime($slot['end_time'])),
            ];
        }, $slots)));
    }

    private function generateSlots(string $startTime, string $endTime, int $intervalMinutes): array
    {
        $slots = [];
        $cursor = strtotime($startTime);
        $end = strtotime($endTime);
        while ($cursor + ($intervalMinutes * 60) <= $end) {
            $slotEnd = $cursor + ($intervalMinutes * 60);
            $slots[] = [
                'start_time' => date('H:i:s', $cursor),
                'end_time' => date('H:i:s', $slotEnd),
            ];
            $cursor = $slotEnd;
        }

        return $slots;
    }

    private function overlaps(string $startA, string $endA, string $startB, string $endB): bool
    {
        return strtotime($startA) < strtotime($endB) && strtotime($endA) > strtotime($startB);
    }
}
