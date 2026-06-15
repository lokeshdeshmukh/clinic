<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Clinic;
use App\Models\Doctor;

final class ClinicDirectoryController extends Controller
{
    public function home(): never
    {
        $clinics = (new Clinic())->publicDirectory();
        $this->view('home', [
            'title' => config('app.name') . ' Appointment Booking',
            'clinics' => array_slice($clinics, 0, 6),
        ]);
    }

    public function index(): never
    {
        $this->view('clinics/index', [
            'title' => 'Find Clinics',
            'clinics' => (new Clinic())->publicDirectory(),
        ]);
    }

    public function showClinic(Request $request, $slug): never
    {
        $clinicModel = new Clinic();
        $clinic = $clinicModel->findBySlug((string) $slug);
        if (!$clinic || $clinic['status'] !== 'active') {
            $this->redirect('/clinics', 'Clinic not found.', 'error');
        }

        $doctorCards = array_map(static function (array $doctor): array {
            $name = trim((string) ($doctor['name'] ?? ''));
            $bio = trim((string) ($doctor['bio'] ?? ''));

            return [
                'id' => (string) ($doctor['id'] ?? ''),
                'name' => $name,
                'specialization' => trim((string) ($doctor['specialization'] ?? '')),
                'profile_photo_path' => trim((string) ($doctor['profile_photo_path'] ?? '')),
                'initial' => $name !== '' ? strtoupper(substr($name, 0, 1)) : 'D',
                'bio_display' => $bio !== '' ? $bio : 'Appointments available for online booking.',
                'fee_display' => number_format((float) ($doctor['consultation_fee'] ?? 0), 2),
            ];
        }, (new Doctor())->forClinicSlug((string) $slug));

        $this->view('clinics/show', [
            'title' => $clinic['name'],
            'clinic' => $clinic,
            'doctors' => $doctorCards,
        ]);
    }

    public function showDoctor(Request $request, $id): never
    {
        $doctor = (new Doctor())->publicFind((int) $id);
        if (!$doctor) {
            $this->redirect('/clinics', 'Doctor not found.', 'error');
        }

        $this->view('clinics/doctor', [
            'title' => $doctor['name'],
            'doctor' => $doctor,
        ]);
    }
}
