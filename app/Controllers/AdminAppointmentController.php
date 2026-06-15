<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Appointment;
use App\Services\AppointmentService;

final class AdminAppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments = new AppointmentService())
    {
    }

    public function index(Request $request): never
    {
        $view = (string) $request->query('view', 'upcoming');
        $this->view('admin/appointments/index', [
            'title' => 'Appointments',
            'appointments' => (new Appointment())->listForClinic((int) Auth::id(), $view),
            'activeView' => $view,
        ]);
    }

    public function cancel(Request $request, $id): never
    {
        try {
            $this->appointments->cancel((int) $id, 'clinic', (int) Auth::id(), trim((string) $request->input('reason')) ?: 'Cancelled by clinic admin.');
        } catch (\Throwable $exception) {
            $this->redirect('/admin/appointments', $exception->getMessage(), 'error');
        }

        $this->redirect('/admin/appointments', 'Appointment cancelled.');
    }

    public function reschedule(Request $request, $id): never
    {
        try {
            $this->appointments->reschedule(
                (int) $id,
                'clinic',
                (int) Auth::id(),
                (string) $request->input('appointment_date'),
                (string) $request->input('start_time')
            );
        } catch (\Throwable $exception) {
            $this->redirect('/admin/appointments', $exception->getMessage(), 'error');
        }

        $this->redirect('/admin/appointments', 'Appointment rescheduled.');
    }

    public function complete(Request $request, $id): never
    {
        try {
            $this->appointments->complete((int) $id, (int) Auth::id());
        } catch (\Throwable $exception) {
            $this->redirect('/admin/appointments', $exception->getMessage(), 'error');
        }

        $this->redirect('/admin/appointments?view=completed', 'Appointment marked as completed.');
    }
}
