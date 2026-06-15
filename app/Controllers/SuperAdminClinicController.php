<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Clinic;
use App\Services\AuthService;

final class SuperAdminClinicController extends Controller
{
    public function __construct(private readonly AuthService $auth = new AuthService())
    {
    }

    public function index(): never
    {
        $this->view('super-admin/dashboard', [
            'title' => 'Platform Clinics',
            'clinics' => (new Clinic())->allForPlatform(),
        ]);
    }

    public function store(Request $request): never
    {
        $data = $request->all();
        Session::flashInput($data);

        foreach (['name', 'address', 'phone', 'email', 'password'] as $field) {
            if (trim((string) ($data[$field] ?? '')) === '') {
                $this->redirect('/super-admin/dashboard', 'Complete all required clinic fields.', 'error');
            }
        }

        if (($data['password'] ?? '') !== ($data['password_confirmation'] ?? '')) {
            $this->redirect('/super-admin/dashboard', 'Clinic password confirmation does not match.', 'error');
        }

        try {
            $this->auth->createClinicBySuperAdmin($data, $request->file('logo'));
        } catch (\Throwable $exception) {
            $this->redirect('/super-admin/dashboard', $exception->getMessage(), 'error');
        }

        $this->redirect('/super-admin/dashboard', 'Clinic created and activated successfully.');
    }
}
