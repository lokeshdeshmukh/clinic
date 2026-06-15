<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\ClinicContext;
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
        $currentClinic = ClinicContext::current();
        $payload = $currentClinic
            ? [(new Clinic())->findPublicBySlug((string) $currentClinic['slug'])]
            : (new Clinic())->publicDirectory();

        $this->json(['data' => array_values(array_filter($payload))]);
    }

    public function clinicDoctors(Request $request, $slug): never
    {
        $currentClinic = ClinicContext::current();
        if ($currentClinic && (string) $currentClinic['slug'] !== (string) $slug) {
            $this->json(['message' => 'Clinic not found for this link.'], 404);
        }

        $this->json(['data' => (new Doctor())->forClinicSlug((string) $slug)]);
    }

    public function show(Request $request, $id): never
    {
        $doctor = (new Doctor())->publicFind((int) $id);
        if (!$doctor) {
            $this->json(['message' => 'Doctor not found.'], 404);
        }

        $currentClinic = ClinicContext::current();
        if ($currentClinic && (int) $doctor['clinic_id'] !== (int) $currentClinic['id']) {
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

        $doctor = (new Doctor())->publicFind((int) $id);
        if (!$doctor) {
            $this->json(['message' => 'Doctor not found.'], 404);
        }

        $currentClinic = ClinicContext::current();
        if ($currentClinic && (int) $doctor['clinic_id'] !== (int) $currentClinic['id']) {
            $this->json(['message' => 'Doctor not found.'], 404);
        }

        $this->json(['data' => $this->availability->getAvailableSlots((int) $id, $date)]);
    }
}
