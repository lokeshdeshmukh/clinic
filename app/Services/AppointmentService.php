<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Appointment;
use App\Models\AppointmentStatusLog;
use App\Models\Doctor;
use App\Models\Patient;
use PDO;
use RuntimeException;

final class AppointmentService
{
    public function __construct(
        private readonly Appointment $appointments = new Appointment(),
        private readonly Doctor $doctors = new Doctor(),
        private readonly Patient $patients = new Patient(),
        private readonly AvailabilityService $availability = new AvailabilityService(),
        private readonly NotificationService $notifications = new NotificationService(),
        private readonly AnalyticsService $analytics = new AnalyticsService(),
        private readonly RevenueService $revenue = new RevenueService()
    ) {
    }

    public function create(int $patientId, int $doctorId, string $date, string $startTime, ?string $notes = null): array
    {
        $doctor = $this->doctors->publicFind($doctorId);
        $patient = $this->patients->findActiveById($patientId);
        if (!$doctor || !$patient) {
            throw new RuntimeException('Unable to book this appointment.');
        }

        $availableSlots = $this->availability->getAvailableSlots($doctorId, $date);
        $slot = $this->findSlot($availableSlots, $startTime);
        if (!$slot) {
            throw new RuntimeException('That time slot is no longer available.');
        }

        $pdo = Database::connection();
        $lockName = sprintf('appointment:%d:%s', $doctorId, $date);
        $now = date('Y-m-d H:i:s');

        try {
            $pdo->beginTransaction();
            $this->acquireLock($pdo, $lockName);
            $guardKey = $this->bookingGuardKey($doctorId, $date, $slot['start_time'], $slot['end_time']);

            $appointmentId = $this->appointments->insert([
                'clinic_id' => $doctor['clinic_id'],
                'doctor_id' => $doctorId,
                'patient_id' => $patientId,
                'appointment_date' => $date,
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'timezone' => config('app.timezone', 'UTC'),
                'consultation_fee' => $doctor['consultation_fee'],
                'status' => 'booked',
                'notes' => $notes ?: null,
                'cancellation_reason' => null,
                'booking_guard_key' => $guardKey,
                'active_booking' => 1,
                'reminder_sent_at' => null,
                'confirmation_sent_at' => null,
                'completed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);

            $this->logStatus($appointmentId, null, 'booked', 'patient', $patientId, 'Appointment booked online.');
            $this->analytics->track('appointment_booked', [
                'clinic_id' => $doctor['clinic_id'],
                'doctor_id' => $doctorId,
                'patient_id' => $patientId,
                'appointment_id' => $appointmentId,
                'event_date' => $date,
            ]);

            $pdo->commit();
            $this->releaseLock($pdo, $lockName);
        } catch (\PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->releaseLock($pdo, $lockName);

            throw new RuntimeException('That time slot was just booked. Please choose another slot.');
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->releaseLock($pdo, $lockName);

            throw $exception;
        }

        $appointment = $this->appointments->findDetailed($appointmentId);
        $confirmationSent = !empty($appointment['patient_email']);
        $this->notifications->sendAppointmentConfirmation($appointment);
        if ($confirmationSent) {
            $this->appointments->updateById($appointmentId, ['confirmation_sent_at' => date('Y-m-d H:i:s')]);
        }

        return $appointment;
    }

    public function cancel(int $appointmentId, string $actorType, int $actorId, string $reason = ''): array
    {
        $appointment = $this->appointments->findDetailed($appointmentId);
        if (!$appointment) {
            throw new RuntimeException('Appointment not found.');
        }

        $this->authorize($appointment, $actorType, $actorId);
        if (in_array($appointment['status'], ['cancelled', 'completed'], true)) {
            throw new RuntimeException('This appointment cannot be cancelled.');
        }

        $now = date('Y-m-d H:i:s');
        $this->appointments->updateById($appointmentId, [
            'status' => 'cancelled',
            'cancellation_reason' => $reason ?: 'Cancelled by ' . $actorType,
            'active_booking' => 0,
            'booking_guard_key' => 'cancelled:' . $appointmentId . ':' . time(),
            'updated_at' => $now,
        ]);
        $this->logStatus($appointmentId, $appointment['status'], 'cancelled', $actorType, $actorId, $reason ?: 'Appointment cancelled.');
        $this->analytics->track('appointment_cancelled', [
            'clinic_id' => $appointment['clinic_id'],
            'doctor_id' => $appointment['doctor_id'],
            'patient_id' => $appointment['patient_id'],
            'appointment_id' => $appointmentId,
            'event_date' => date('Y-m-d'),
        ]);

        $updated = $this->appointments->findDetailed($appointmentId);
        $this->notifications->sendAppointmentCancellation($updated);

        return $updated;
    }

    public function reschedule(int $appointmentId, string $actorType, int $actorId, string $newDate, string $newStartTime): array
    {
        $appointment = $this->appointments->findDetailed($appointmentId);
        if (!$appointment) {
            throw new RuntimeException('Appointment not found.');
        }

        $this->authorize($appointment, $actorType, $actorId);
        if (in_array($appointment['status'], ['cancelled', 'completed'], true)) {
            throw new RuntimeException('This appointment cannot be rescheduled.');
        }

        $availableSlots = $this->availability->getAvailableSlots((int) $appointment['doctor_id'], $newDate);
        $slot = $this->findSlot($availableSlots, $newStartTime);
        if (!$slot) {
            throw new RuntimeException('The selected new slot is unavailable.');
        }

        $oldDate = (string) $appointment['appointment_date'];
        $oldTime = (string) $appointment['start_time'];
        $newGuard = $this->bookingGuardKey((int) $appointment['doctor_id'], $newDate, $slot['start_time'], $slot['end_time']);
        $pdo = Database::connection();
        $lockName = sprintf('appointment:%d:%s', (int) $appointment['doctor_id'], $newDate);

        try {
            $pdo->beginTransaction();
            $this->acquireLock($pdo, $lockName);

            $this->appointments->updateById($appointmentId, [
                'appointment_date' => $newDate,
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'booking_guard_key' => $newGuard,
                'status' => 'booked',
                'cancellation_reason' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->logStatus(
                $appointmentId,
                $appointment['status'],
                'booked',
                $actorType,
                $actorId,
                sprintf('Rescheduled from %s %s to %s %s.', $oldDate, $oldTime, $newDate, $slot['start_time'])
            );
            $this->analytics->track('appointment_rescheduled', [
                'clinic_id' => $appointment['clinic_id'],
                'doctor_id' => $appointment['doctor_id'],
                'patient_id' => $appointment['patient_id'],
                'appointment_id' => $appointmentId,
                'event_date' => $newDate,
                'metadata' => ['old_date' => $oldDate, 'old_time' => $oldTime],
            ]);

            $pdo->commit();
            $this->releaseLock($pdo, $lockName);
        } catch (\PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->releaseLock($pdo, $lockName);

            throw new RuntimeException('The selected slot was just taken. Please choose another time.');
        } catch (\Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->releaseLock($pdo, $lockName);
            throw $exception;
        }

        $updated = $this->appointments->findDetailed($appointmentId);
        $this->notifications->sendAppointmentReschedule($updated, $oldDate, $oldTime);

        return $updated;
    }

    public function complete(int $appointmentId, int $clinicId): array
    {
        $appointment = $this->appointments->findDetailed($appointmentId);
        if (!$appointment || (int) $appointment['clinic_id'] !== $clinicId) {
            throw new RuntimeException('Appointment not found.');
        }

        if ($appointment['status'] === 'completed') {
            return $appointment;
        }

        $this->appointments->updateById($appointmentId, [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->logStatus($appointmentId, $appointment['status'], 'completed', 'clinic', $clinicId, 'Appointment marked complete.');
        $updated = $this->appointments->findDetailed($appointmentId);
        $this->revenue->recordCompletedAppointment($updated);
        $this->analytics->track('appointment_completed', [
            'clinic_id' => $updated['clinic_id'],
            'doctor_id' => $updated['doctor_id'],
            'patient_id' => $updated['patient_id'],
            'appointment_id' => $updated['id'],
            'event_date' => $updated['appointment_date'],
        ]);

        return $updated;
    }

    private function authorize(array $appointment, string $actorType, int $actorId): void
    {
        $allowed = ($actorType === 'clinic' && (int) $appointment['clinic_id'] === $actorId)
            || ($actorType === 'patient' && (int) $appointment['patient_id'] === $actorId);

        if (!$allowed) {
            throw new RuntimeException('You are not allowed to modify this appointment.');
        }
    }

    private function logStatus(int $appointmentId, ?string $previousStatus, string $newStatus, string $actorType, int $actorId, string $note): void
    {
        (new AppointmentStatusLog())->insert([
            'appointment_id' => $appointmentId,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'changed_by_type' => $actorType,
            'changed_by_id' => $actorId,
            'note' => $note,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function bookingGuardKey(int $doctorId, string $date, string $startTime, string $endTime): string
    {
        return implode('|', [$doctorId, $date, $startTime, $endTime]);
    }

    private function findSlot(array $slots, string $startTime): ?array
    {
        foreach ($slots as $slot) {
            if ($slot['start_time'] === $startTime) {
                return $slot;
            }
        }

        return null;
    }

    private function acquireLock(PDO $pdo, string $lockName): void
    {
        $statement = $pdo->prepare('SELECT GET_LOCK(:lock_name, 10)');
        $statement->execute(['lock_name' => $lockName]);
        if ((int) $statement->fetchColumn() !== 1) {
            throw new RuntimeException('Could not acquire appointment lock. Please try again.');
        }
    }

    private function releaseLock(PDO $pdo, string $lockName): void
    {
        $statement = $pdo->prepare('SELECT RELEASE_LOCK(:lock_name)');
        $statement->execute(['lock_name' => $lockName]);
    }
}
