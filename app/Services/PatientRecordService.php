<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientRecord;
use RuntimeException;

final class PatientRecordService
{
    public function __construct(
        private readonly Patient $patients = new Patient(),
        private readonly Appointment $appointments = new Appointment(),
        private readonly PatientRecord $records = new PatientRecord(),
        private readonly UploadService $uploads = new UploadService(),
        private readonly PrescriptionParserService $parser = new PrescriptionParserService()
    ) {
    }

    public function createForClinic(int $clinicId, int $patientId, array $data, ?array $file): array
    {
        $patient = $this->patients->findForClinic($patientId, $clinicId);
        if (!$patient) {
            throw new RuntimeException('Patient not found for this clinic.');
        }

        $appointmentId = (int) ($data['appointment_id'] ?? 0);
        $appointment = null;
        if ($appointmentId > 0) {
            $appointment = $this->appointments->findDetailed($appointmentId);
            if (!$appointment || (int) $appointment['clinic_id'] !== $clinicId || (int) $appointment['patient_id'] !== $patientId) {
                throw new RuntimeException('Selected appointment does not belong to this patient.');
            }

            if ((string) ($appointment['status'] ?? '') !== 'completed') {
                throw new RuntimeException('Mark the appointment completed before uploading a visit record.');
            }
        }

        $recordType = (string) ($data['record_type'] ?? 'visit_note');
        if (!in_array($recordType, ['visit_note', 'prescription', 'lab_report', 'discharge_summary', 'other'], true)) {
            throw new RuntimeException('Choose a valid record type.');
        }

        $visitSummary = trim((string) ($data['visit_summary'] ?? ''));
        $manualText = trim((string) ($data['transcribed_text'] ?? ''));
        if ($visitSummary === '' && $manualText === '' && ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)) {
            throw new RuntimeException('Add a summary, prescription text, or a document to save this record.');
        }

        $recordedAt = $this->normalizeRecordedAt((string) ($data['recorded_at'] ?? ''));
        $upload = $this->uploads->storePatientRecordDocument($file, 'patient-records');
        $analysis = $this->parser->analyze($upload['absolute_path'] ?? null, $upload['mime_type'] ?? null, $recordType, $manualText);

        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            $title = $this->defaultTitle($recordType, $appointment, $recordedAt);
        }

        $now = date('Y-m-d H:i:s');
        $recordId = $this->records->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'appointment_id' => $appointmentId > 0 ? $appointmentId : null,
            'doctor_id' => $appointment ? (int) $appointment['doctor_id'] : null,
            'record_type' => $recordType,
            'title' => $title,
            'visit_summary' => $visitSummary !== '' ? $visitSummary : null,
            'document_path' => $upload['path'] ?? null,
            'document_mime_type' => $upload['mime_type'] ?? null,
            'original_filename' => $upload['original_filename'] ?? null,
            'extracted_text' => $analysis['extracted_text'],
            'extracted_medications' => $analysis['medications'] !== [] ? json_encode($analysis['medications'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'ocr_status' => (string) $analysis['ocr_status'],
            'ocr_provider' => $analysis['ocr_provider'],
            'ocr_error' => $analysis['ocr_error'],
            'recorded_at' => $recordedAt,
            'created_by_type' => 'clinic',
            'created_by_id' => $clinicId,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]);

        $record = $this->records->findForClinic($recordId, $clinicId);
        if ($record === null) {
            throw new RuntimeException('Visit record was saved but could not be reloaded.');
        }

        return $record;
    }

    private function normalizeRecordedAt(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return date('Y-m-d H:i:s');
        }

        $timestamp = strtotime(str_replace('T', ' ', $value));
        if ($timestamp === false) {
            throw new RuntimeException('Recorded date is invalid.');
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function defaultTitle(string $recordType, ?array $appointment, string $recordedAt): string
    {
        $labels = [
            'visit_note' => 'Visit note',
            'prescription' => 'Prescription',
            'lab_report' => 'Lab report',
            'discharge_summary' => 'Discharge summary',
            'other' => 'Patient record',
        ];

        $label = $labels[$recordType] ?? 'Patient record';
        if ($appointment) {
            return $label . ' • ' . date('d M Y', strtotime((string) $appointment['appointment_date']));
        }

        return $label . ' • ' . date('d M Y', strtotime($recordedAt));
    }
}
