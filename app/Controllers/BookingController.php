<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Doctor;
use App\Services\AppointmentService;

final class BookingController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments = new AppointmentService())
    {
    }

    public function showBooking(Request $request, $id): never
    {
        $doctor = (new Doctor())->publicFind((int) $id);
        if (!$doctor) {
            $this->redirect('/clinics', 'Doctor not found.', 'error');
        }

        $this->view('patient/book', [
            'title' => 'Book Appointment',
            'doctor' => $doctor,
            'patientLoggedIn' => Auth::check('patient'),
        ]);
    }

    public function book(Request $request, $id): never
    {
        $doctorId = (int) $id;
        $patientId = (int) Auth::id();

        try {
            $this->appointments->create(
                $patientId,
                $doctorId,
                (string) $request->input('appointment_date'),
                (string) $request->input('start_time'),
                trim((string) $request->input('notes')) ?: null
            );
        } catch (\Throwable $exception) {
            $this->redirect('/doctors/' . $doctorId . '/book', $exception->getMessage(), 'error');
        }

        $this->redirect('/patient/dashboard', 'Appointment booked successfully.');
    }
}
