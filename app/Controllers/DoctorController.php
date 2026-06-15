<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Doctor;
use App\Services\UploadService;

final class DoctorController extends Controller
{
    public function __construct(
        private readonly Doctor $doctors = new Doctor(),
        private readonly UploadService $uploads = new UploadService()
    ) {
    }

    public function index(): never
    {
        $clinicId = (int) Auth::id();
        $this->view('admin/doctors/index', [
            'title' => 'Doctor Management',
            'doctors' => $this->doctors->forClinic($clinicId),
        ]);
    }

    public function create(): never
    {
        $this->view('admin/doctors/form', [
            'title' => 'Add Doctor',
            'doctor' => null,
        ]);
    }

    public function store(Request $request): never
    {
        $clinicId = (int) Auth::id();
        $now = date('Y-m-d H:i:s');

        try {
            $this->doctors->insert([
                'clinic_id' => $clinicId,
                'name' => trim((string) $request->input('name')),
                'specialization' => trim((string) $request->input('specialization')),
                'consultation_fee' => (float) $request->input('consultation_fee', 0),
                'slot_duration_minutes' => (int) $request->input('slot_duration_minutes', config('app.default_slot_duration', 30)),
                'bio' => trim((string) $request->input('bio')) ?: null,
                'profile_photo_path' => $this->uploads->store($request->file('profile_photo'), 'doctors'),
                'status' => (string) $request->input('status', 'active'),
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);
        } catch (\Throwable $exception) {
            $this->redirect('/admin/doctors/create', $exception->getMessage(), 'error');
        }

        $this->redirect('/admin/doctors', 'Doctor added successfully.');
    }

    public function edit(Request $request, $id): never
    {
        $doctor = $this->doctors->findByIdForClinic((int) $id, (int) Auth::id());
        if (!$doctor) {
            $this->redirect('/admin/doctors', 'Doctor not found.', 'error');
        }

        $this->view('admin/doctors/form', [
            'title' => 'Edit Doctor',
            'doctor' => $doctor,
        ]);
    }

    public function update(Request $request, $id): never
    {
        $clinicId = (int) Auth::id();
        $doctor = $this->doctors->findByIdForClinic((int) $id, $clinicId);
        if (!$doctor) {
            $this->redirect('/admin/doctors', 'Doctor not found.', 'error');
        }

        try {
            $photo = $this->uploads->store($request->file('profile_photo'), 'doctors');
            $this->doctors->updateById((int) $id, [
                'name' => trim((string) $request->input('name')),
                'specialization' => trim((string) $request->input('specialization')),
                'consultation_fee' => (float) $request->input('consultation_fee', $doctor['consultation_fee']),
                'slot_duration_minutes' => (int) $request->input('slot_duration_minutes', $doctor['slot_duration_minutes']),
                'bio' => trim((string) $request->input('bio')) ?: null,
                'profile_photo_path' => $photo ?: $doctor['profile_photo_path'],
                'status' => (string) $request->input('status', $doctor['status']),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $exception) {
            $this->redirect('/admin/doctors/' . $id . '/edit', $exception->getMessage(), 'error');
        }

        $this->redirect('/admin/doctors', 'Doctor updated successfully.');
    }

    public function delete(Request $request, $id): never
    {
        $doctor = $this->doctors->findByIdForClinic((int) $id, (int) Auth::id());
        if (!$doctor) {
            $this->redirect('/admin/doctors', 'Doctor not found.', 'error');
        }

        $this->doctors->softDelete((int) $id);
        $this->redirect('/admin/doctors', 'Doctor deleted successfully.');
    }
}
