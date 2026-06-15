<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Models\Clinic;
use App\Models\Patient;
use RuntimeException;

final class AuthService
{
    public function __construct(
        private readonly Clinic $clinics = new Clinic(),
        private readonly Patient $patients = new Patient(),
        private readonly UploadService $uploads = new UploadService(),
        private readonly NotificationService $notifications = new NotificationService(),
        private readonly AnalyticsService $analytics = new AnalyticsService()
    ) {
    }

    public function registerClinic(array $data, ?array $logoFile): array
    {
        if ($this->clinics->findByEmail((string) $data['email'])) {
            throw new RuntimeException('A clinic with that email already exists.');
        }

        $slug = $this->generateUniqueSlug((string) $data['name']);
        $now = date('Y-m-d H:i:s');
        $clinicId = $this->clinics->insert([
            'name' => trim((string) $data['name']),
            'slug' => $slug,
            'address' => trim((string) $data['address']),
            'phone' => trim((string) $data['phone']),
            'email' => trim((string) $data['email']),
            'password_hash' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
            'logo_path' => $this->uploads->store($logoFile, 'clinics'),
            'email_verified_at' => null,
            'verification_token' => bin2hex(random_bytes(24)),
            'reset_token' => null,
            'reset_token_expires_at' => null,
            'status' => 'pending_verification',
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);

        $clinic = $this->clinics->findActiveById($clinicId);
        $this->notifications->sendClinicVerification($clinic);

        return $clinic;
    }

    public function verifyClinic(string $token): bool
    {
        $clinic = $this->clinics->findByVerificationToken($token);
        if (!$clinic) {
            return false;
        }

        $this->clinics->updateById((int) $clinic['id'], [
            'verification_token' => null,
            'email_verified_at' => date('Y-m-d H:i:s'),
            'status' => 'active',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $updatedClinic = $this->clinics->findActiveById((int) $clinic['id']);
        $this->notifications->sendClinicWelcome($updatedClinic);

        return true;
    }

    public function attemptClinicLogin(string $email, string $password): bool
    {
        $clinic = $this->clinics->findByEmail($email);
        if (!$clinic || !password_verify($password, (string) $clinic['password_hash'])) {
            return false;
        }

        if (($clinic['status'] ?? '') !== 'active') {
            throw new RuntimeException('Verify your email before signing in.');
        }

        Auth::login('clinic', (int) $clinic['id']);
        return true;
    }

    public function registerPatient(array $data): array
    {
        if ($this->patients->findByEmail((string) $data['email'])) {
            throw new RuntimeException('A patient with that email already exists.');
        }

        $now = date('Y-m-d H:i:s');
        $patientId = $this->patients->insert([
            'first_name' => trim((string) $data['first_name']),
            'last_name' => trim((string) $data['last_name']),
            'email' => trim((string) $data['email']),
            'phone' => trim((string) $data['phone']),
            'password_hash' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
            'date_of_birth' => $data['date_of_birth'] ?: null,
            'gender' => $data['gender'] ?: null,
            'reset_token' => null,
            'reset_token_expires_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);

        $patient = $this->patients->findActiveById($patientId);
        $this->notifications->sendPatientWelcome($patient);
        $this->analytics->track('patient_registered', ['patient_id' => $patientId, 'event_date' => date('Y-m-d')]);

        return $patient;
    }

    public function attemptPatientLogin(string $email, string $password): bool
    {
        $patient = $this->patients->findByEmail($email);
        if (!$patient || !password_verify($password, (string) $patient['password_hash'])) {
            return false;
        }

        Auth::login('patient', (int) $patient['id']);
        return true;
    }

    public function sendClinicResetLink(string $email): void
    {
        $clinic = $this->clinics->findByEmail($email);
        if (!$clinic) {
            return;
        }

        $token = bin2hex(random_bytes(24));
        $this->clinics->updateById((int) $clinic['id'], [
            'reset_token' => $token,
            'reset_token_expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->notifications->sendPasswordReset(
            $clinic['email'],
            url('/clinic/reset-password?token=' . urlencode($token)),
            'clinic',
            (int) $clinic['id'],
            null
        );
    }

    public function resetClinicPassword(string $token, string $password): bool
    {
        $clinic = $this->clinics->findByResetToken($token);
        if (!$clinic || strtotime((string) $clinic['reset_token_expires_at']) < time()) {
            return false;
        }

        $this->clinics->updateById((int) $clinic['id'], [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_token_expires_at' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    public function sendPatientResetLink(string $email): void
    {
        $patient = $this->patients->findByEmail($email);
        if (!$patient) {
            return;
        }

        $token = bin2hex(random_bytes(24));
        $this->patients->updateById((int) $patient['id'], [
            'reset_token' => $token,
            'reset_token_expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->notifications->sendPasswordReset(
            $patient['email'],
            url('/patient/reset-password?token=' . urlencode($token)),
            'patient',
            null,
            (int) $patient['id']
        );
    }

    public function resetPatientPassword(string $token, string $password): bool
    {
        $patient = $this->patients->findByResetToken($token);
        if (!$patient || strtotime((string) $patient['reset_token_expires_at']) < time()) {
            return false;
        }

        $this->patients->updateById((int) $patient['id'], [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_token_expires_at' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($name)), '-');
        $slug = $base !== '' ? $base : 'clinic';
        $suffix = 1;

        while ($this->clinics->slugExists($slug)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}
