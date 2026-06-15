<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\ExportService;
use App\Services\ReportService;

final class ReportsController extends Controller
{
    public function __construct(
        private readonly ReportService $reports = new ReportService(),
        private readonly ExportService $exports = new ExportService()
    ) {
    }

    public function index(Request $request): never
    {
        $type = (string) $request->query('type', 'appointments');
        $rows = $this->rowsForType($type, (int) Auth::id());

        $this->view('admin/reports/index', [
            'title' => 'Reports',
            'type' => $type,
            'rows' => $rows,
        ]);
    }

    public function export(Request $request): never
    {
        $type = (string) $request->query('type', 'appointments');
        $format = (string) $request->query('format', 'csv');
        $rows = $this->rowsForType($type, (int) Auth::id());
        $filename = $type . '-report-' . date('Y-m-d');

        if ($format === 'excel') {
            Response::download($filename . '.xls', 'application/vnd.ms-excel', $this->exports->excel($rows, ucfirst($type) . ' Report'));
        }

        if ($format === 'pdf') {
            Response::download($filename . '.pdf', 'application/pdf', $this->exports->pdf($rows, ucfirst($type) . ' Report'));
        }

        Response::download($filename . '.csv', 'text/csv; charset=utf-8', $this->exports->csv($rows));
    }

    private function rowsForType(string $type, int $clinicId): array
    {
        return match ($type) {
            'revenue' => $this->reports->revenueReport($clinicId),
            'doctor-performance' => $this->reports->doctorPerformanceReport($clinicId),
            default => $this->reports->appointmentReport($clinicId),
        };
    }
}
