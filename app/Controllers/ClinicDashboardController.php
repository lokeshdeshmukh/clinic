<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Services\DashboardService;

final class ClinicDashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard = new DashboardService())
    {
    }

    public function index(): never
    {
        $clinicId = (int) Auth::id();
        $clinic = Auth::user();
        $data = $this->dashboard->dataForClinic($clinicId);

        $this->view('admin/dashboard', [
            'title' => 'Clinic Dashboard',
            'clinic' => $clinic,
            'dashboard' => $data,
        ]);
    }
}
