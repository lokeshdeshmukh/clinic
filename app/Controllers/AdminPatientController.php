<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientRecord;
use App\Services\PatientRecordService;

final class AdminPatientController extends Controller
{
    public function __construct(
        private readonly Patient $patients = new Patient(),
        private readonly Appointment $appointments = new Appointment(),
        private readonly PatientRecord $records = new PatientRecord(),
        private readonly PatientRecordService $patientRecords = new PatientRecordService()
    ) {
    }

    public function index(): never
    {
        $clinicId = (int) Auth::id();
        $this->view('admin/patients/index', [
            'title' => 'Patient Management',
            'patients' => $this->patients->forClinic($clinicId),
        ]);
    }

    public function show(Request $request, $id): never
    {
        $clinicId = (int) Auth::id();
        $patientId = (int) $id;
        $patient = $this->patients->findForClinic($patientId, $clinicId);
        if (!$patient) {
            $this->redirect('/admin/patients', 'Patient not found.', 'error');
        }

        $appointments = $this->appointments->listForPatientInClinic($patientId, $clinicId);
        $completedAppointments = array_values(array_filter($appointments, static fn (array $appointment): bool => (string) ($appointment['status'] ?? '') === 'completed'));
        $records = array_map([$this, 'hydrateRecord'], $this->records->listForClinicPatient($clinicId, $patientId));
        $selectedAppointmentId = (int) $request->query('appointment', 0);

        $this->view('admin/patients/show', [
            'title' => 'Patient Records',
            'patient' => $patient,
            'appointments' => $appointments,
            'completedAppointments' => $completedAppointments,
            'records' => $records,
            'selectedAppointmentId' => $selectedAppointmentId,
            'ocrEnabled' => (bool) config('services.prescription_ocr.enabled', false),
        ]);
    }

    public function storeRecord(Request $request, $id): never
    {
        $clinicId = (int) Auth::id();
        $patientId = (int) $id;
        $data = $request->all();
        Session::flashInput($data);

        try {
            $this->patientRecords->createForClinic($clinicId, $patientId, $data, $request->file('document'));
        } catch (\Throwable $exception) {
            $appointmentId = (int) ($data['appointment_id'] ?? 0);
            $redirect = '/admin/patients/' . $patientId;
            if ($appointmentId > 0) {
                $redirect .= '?appointment=' . $appointmentId;
            }

            $this->redirect($redirect, $exception->getMessage(), 'error');
        }

        $this->redirect('/admin/patients/' . $patientId, 'Visit record saved successfully.');
    }

    public function downloadRecord($id): never
    {
        $clinicId = (int) Auth::id();
        $record = $this->records->findForClinic((int) $id, $clinicId);
        if (!$record || empty($record['document_path'])) {
            $this->redirect('/admin/patients', 'Patient record file not found.', 'error');
        }

        $absolutePath = storage_path((string) $record['document_path']);
        if (!is_file($absolutePath)) {
            $this->redirect('/admin/patients/' . (int) $record['patient_id'], 'Patient record file is missing from storage.', 'error');
        }

        $filename = (string) ($record['original_filename'] ?: basename($absolutePath));
        $safeFilename = str_replace(['"', "\r", "\n"], ['', '', ''], $filename);

        header('Content-Type: ' . ((string) ($record['document_mime_type'] ?? 'application/octet-stream')));
        header('Content-Disposition: inline; filename="' . $safeFilename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
        header('Content-Length: ' . (string) filesize($absolutePath));
        header('X-Content-Type-Options: nosniff');
        readfile($absolutePath);
        exit;
    }

    private function hydrateRecord(array $record): array
    {
        $record['medications'] = json_decode((string) ($record['extracted_medications'] ?? ''), true);
        if (!is_array($record['medications'])) {
            $record['medications'] = [];
        }

        $record['is_image'] = str_starts_with((string) ($record['document_mime_type'] ?? ''), 'image/');

        return $record;
    }
}
