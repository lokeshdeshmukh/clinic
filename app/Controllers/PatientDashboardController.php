<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\ClinicContext;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Appointment;
use App\Services\AppointmentService;

final class PatientDashboardController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments = new AppointmentService())
    {
    }

    public function index(): never
    {
        $appointments = new Appointment();
        $currentClinic = ClinicContext::current();
        $allAppointments = $currentClinic
            ? $appointments->listForPatientInClinic((int) Auth::id(), (int) $currentClinic['id'])
            : $appointments->listForPatient((int) Auth::id());
        $upcoming = array_values(array_filter($allAppointments, static fn (array $appointment): bool => $appointment['appointment_date'] >= date('Y-m-d') && in_array($appointment['status'], ['booked', 'confirmed'], true)));
        $history = array_values(array_filter($allAppointments, static fn (array $appointment): bool => !($appointment['appointment_date'] >= date('Y-m-d') && in_array($appointment['status'], ['booked', 'confirmed'], true))));

        $this->view('patient/dashboard', [
            'title' => 'Patient Dashboard',
            'upcomingAppointments' => $upcoming,
            'historyAppointments' => $history,
        ]);
    }

    public function cancel(Request $request, $id): never
    {
        $currentClinic = ClinicContext::current();
        if ($currentClinic) {
            $appointment = (new Appointment())->findForPatient((int) $id, (int) Auth::id());
            if (!$appointment || (int) $appointment['clinic_id'] !== (int) $currentClinic['id']) {
                $this->redirect('/patient/dashboard', 'Appointment not found for this clinic.', 'error');
            }
        }

        try {
            $this->appointments->cancel((int) $id, 'patient', (int) Auth::id(), trim((string) $request->input('reason')) ?: 'Cancelled by patient.');
        } catch (\Throwable $exception) {
            $this->redirect('/patient/dashboard', $exception->getMessage(), 'error');
        }

        $this->redirect('/patient/dashboard', 'Appointment cancelled.');
    }

    public function reschedule(Request $request, $id): never
    {
        $currentClinic = ClinicContext::current();
        if ($currentClinic) {
            $appointment = (new Appointment())->findForPatient((int) $id, (int) Auth::id());
            if (!$appointment || (int) $appointment['clinic_id'] !== (int) $currentClinic['id']) {
                $this->redirect('/patient/dashboard', 'Appointment not found for this clinic.', 'error');
            }
        }

        try {
            $this->appointments->reschedule(
                (int) $id,
                'patient',
                (int) Auth::id(),
                (string) $request->input('appointment_date'),
                (string) $request->input('start_time')
            );
        } catch (\Throwable $exception) {
            $this->redirect('/patient/dashboard', $exception->getMessage(), 'error');
        }

        $this->redirect('/patient/dashboard', 'Appointment rescheduled.');
    }
}
