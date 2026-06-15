<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Appointment;
use App\Services\AppointmentService;

final class AppointmentApiController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments = new AppointmentService())
    {
    }

    public function adminIndex(Request $request): never
    {
        $this->json([
            'data' => (new Appointment())->listForClinic((int) Auth::id(), (string) $request->query('view', 'upcoming')),
        ]);
    }

    public function store(Request $request): never
    {
        try {
            $appointment = $this->appointments->create(
                (int) Auth::id(),
                (int) $request->input('doctor_id'),
                (string) $request->input('appointment_date'),
                (string) $request->input('start_time'),
                trim((string) $request->input('notes')) ?: null
            );
        } catch (\Throwable $exception) {
            $this->json(['message' => $exception->getMessage()], 422);
        }

        $this->json(['data' => $appointment], 201);
    }

    public function cancel(Request $request, $id): never
    {
        $actorType = Auth::guard();
        $actorId = Auth::id();
        if (!$actorType || !$actorId) {
            $this->json(['message' => 'Authentication required.'], 401);
        }

        try {
            $appointment = $this->appointments->cancel(
                (int) $id,
                $actorType,
                (int) $actorId,
                trim((string) $request->input('reason')) ?: 'Cancelled via API.'
            );
        } catch (\Throwable $exception) {
            $this->json(['message' => $exception->getMessage()], 422);
        }

        $this->json(['data' => $appointment]);
    }
}
