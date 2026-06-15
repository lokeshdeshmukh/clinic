<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\AvailabilityRule;
use App\Models\Doctor;
use App\Services\AvailabilityService;

final class AvailabilityController extends Controller
{
    public function __construct(private readonly AvailabilityService $availability = new AvailabilityService())
    {
    }

    public function index(): never
    {
        $clinicId = (int) Auth::id();
        $rules = (new AvailabilityRule())->forClinic($clinicId);
        $events = array_values(array_filter(array_map(static function (array $rule): ?array {
            $title = ucfirst(str_replace('_', ' ', (string) $rule['rule_type'])) . ': ' . $rule['doctor_name'];
            $color = match ($rule['rule_type']) {
                'holiday' => '#dc2626',
                'blocked_slot' => '#f59e0b',
                'date_override' => '#0891b2',
                default => '#2563eb',
            };

            if (!$rule['specific_date']) {
                return null;
            }

            return [
                'title' => $title,
                'start' => $rule['specific_date'] . 'T' . ($rule['start_time'] ?: '00:00:00'),
                'end' => $rule['specific_date'] . 'T' . ($rule['end_time'] ?: '23:59:59'),
                'color' => $color,
            ];
        }, $rules)));

        $this->view('admin/availability/index', [
            'title' => 'Doctor Availability',
            'doctors' => (new Doctor())->forClinic($clinicId),
            'rules' => $rules,
            'events' => $events,
        ]);
    }

    public function store(Request $request): never
    {
        try {
            $this->availability->createRule((int) Auth::id(), $request->all());
        } catch (\Throwable $exception) {
            $this->redirect('/admin/availability', $exception->getMessage(), 'error');
        }

        $this->redirect('/admin/availability', 'Availability rule saved.');
    }

    public function delete(Request $request, $id): never
    {
        try {
            $this->availability->deleteRule((int) Auth::id(), (int) $id);
        } catch (\Throwable $exception) {
            $this->redirect('/admin/availability', $exception->getMessage(), 'error');
        }

        $this->redirect('/admin/availability', 'Availability rule removed.');
    }
}
