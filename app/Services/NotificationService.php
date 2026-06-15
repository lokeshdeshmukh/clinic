<?php

declare(strict_types=1);

namespace App\Services;

final class NotificationService
{
    public function __construct(private readonly MailerService $mailer = new MailerService())
    {
    }

    public function sendClinicVerification(array $clinic): void
    {
        $this->mailer->send(
            $clinic['email'],
            'Verify your clinic account',
            'clinic-verification',
            ['clinic' => $clinic, 'verificationUrl' => url('/clinic/verify?token=' . urlencode((string) $clinic['verification_token']))],
            ['clinic_id' => $clinic['id']]
        );
    }

    public function sendClinicWelcome(array $clinic): void
    {
        $this->mailer->send(
            $clinic['email'],
            'Welcome to ' . config('app.name'),
            'clinic-welcome',
            ['clinic' => $clinic],
            ['clinic_id' => $clinic['id']]
        );
    }

    public function sendPatientWelcome(array $patient): void
    {
        $this->mailer->send(
            $patient['email'],
            'Welcome to ' . config('app.name'),
            'patient-welcome',
            ['patient' => $patient],
            ['patient_id' => $patient['id']]
        );
    }

    public function sendPasswordReset(string $email, string $resetUrl, string $userType, ?int $clinicId = null, ?int $patientId = null): void
    {
        $this->mailer->send(
            $email,
            'Reset your password',
            'password-reset',
            ['resetUrl' => $resetUrl, 'userType' => $userType],
            ['clinic_id' => $clinicId, 'patient_id' => $patientId]
        );
    }

    public function sendAppointmentConfirmation(array $appointment): void
    {
        $this->mailer->send(
            $appointment['patient_email'],
            'Appointment confirmed with ' . $appointment['doctor_name'],
            'appointment-confirmation',
            ['appointment' => $appointment],
            ['clinic_id' => $appointment['clinic_id'], 'patient_id' => $appointment['patient_id'], 'appointment_id' => $appointment['id']]
        );
    }

    public function sendAppointmentCancellation(array $appointment): void
    {
        $this->mailer->send(
            $appointment['patient_email'],
            'Appointment cancelled',
            'appointment-cancellation',
            ['appointment' => $appointment],
            ['clinic_id' => $appointment['clinic_id'], 'patient_id' => $appointment['patient_id'], 'appointment_id' => $appointment['id']]
        );
    }

    public function sendAppointmentReschedule(array $appointment, string $oldDate, string $oldTime): void
    {
        $this->mailer->send(
            $appointment['patient_email'],
            'Appointment rescheduled',
            'appointment-reschedule',
            ['appointment' => $appointment, 'oldDate' => $oldDate, 'oldTime' => $oldTime],
            ['clinic_id' => $appointment['clinic_id'], 'patient_id' => $appointment['patient_id'], 'appointment_id' => $appointment['id']]
        );
    }

    public function sendAppointmentReminder(array $appointment): void
    {
        $this->mailer->send(
            $appointment['patient_email'],
            'Appointment reminder',
            'appointment-reminder',
            ['appointment' => $appointment],
            ['clinic_id' => $appointment['clinic_id'], 'patient_id' => $appointment['patient_id'], 'appointment_id' => $appointment['id']]
        );
    }
}
