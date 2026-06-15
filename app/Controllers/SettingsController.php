<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Clinic;
use App\Models\SystemSetting;
use App\Services\EnvFileService;
use App\Services\UploadService;

final class SettingsController extends Controller
{
    public function __construct(
        private readonly UploadService $uploads = new UploadService(),
        private readonly EnvFileService $env = new EnvFileService()
    )
    {
    }

    public function edit(): never
    {
        $clinic = Auth::user();
        $settings = (new SystemSetting())->getForClinic((int) Auth::id());
        $this->view('admin/settings/edit', [
            'title' => 'Clinic Settings',
            'clinic' => $clinic,
            'settings' => $settings,
            'deployToken' => (string) env('DEPLOY_TOKEN', ''),
            'deployHookUrl' => rtrim((string) config('app.url'), '/') . '/deploy/run-updates?token=' . urlencode((string) env('DEPLOY_TOKEN', '')),
        ]);
    }

    public function update(Request $request): never
    {
        $clinicId = (int) Auth::id();
        $clinic = (new Clinic())->findActiveById($clinicId);
        if (!$clinic) {
            $this->redirect('/clinic/login', 'Clinic account not found.', 'error');
        }

        try {
            $logoPath = $this->uploads->store($request->file('logo'), 'clinics');
            (new Clinic())->updateById($clinicId, [
                'name' => trim((string) $request->input('name')),
                'address' => trim((string) $request->input('address')),
                'phone' => trim((string) $request->input('phone')),
                'email' => trim((string) $request->input('email')),
                'logo_path' => $logoPath ?: $clinic['logo_path'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $settingsModel = new SystemSetting();
            $settingsModel->upsert($clinicId, 'appointment_reminder_hours', (string) $request->input('appointment_reminder_hours', '24'), 'integer');
            $settingsModel->upsert($clinicId, 'currency', (string) $request->input('currency', 'INR'));

            $deployToken = trim((string) $request->input('deploy_token'));
            if ($deployToken === '') {
                $deployToken = (string) env('DEPLOY_TOKEN', '');
            }
            if ($deployToken === '') {
                $deployToken = bin2hex(random_bytes(24));
            }

            $this->env->set([
                'DEPLOY_TOKEN' => $deployToken,
            ]);
        } catch (\Throwable $exception) {
            $this->redirect('/admin/settings', $exception->getMessage(), 'error');
        }

        $this->redirect('/admin/settings', 'Settings saved successfully.');
    }
}
