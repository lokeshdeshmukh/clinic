<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Services\AvailabilityService;

final class DoctorApiController extends Controller
{
    public function __construct(private readonly AvailabilityService $availability = new AvailabilityService())
    {
    }

    public function clinics(): never
    {
        $this->json(['data' => (new Clinic())->publicDirectory()]);
    }

    public function clinicDoctors(Request $request, $slug): never
    {
        $this->json(['data' => (new Doctor())->forClinicSlug((string) $slug)]);
    }

    public function show(Request $request, $id): never
    {
        $doctor = (new Doctor())->publicFind((int) $id);
        if (!$doctor) {
            $this->json(['message' => 'Doctor not found.'], 404);
        }

        $this->json(['data' => $doctor]);
    }

    public function slots(Request $request, $id): never
    {
        $date = (string) $request->query('date', '');
        if ($date === '') {
            $this->json(['message' => 'Date is required.'], 422);
        }

        $this->json(['data' => $this->availability->getAvailableSlots((int) $id, $date)]);
    }
}
