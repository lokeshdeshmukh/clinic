<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Services\SuperAdminService;

final class SuperAdminAuthController extends Controller
{
    public function __construct(private readonly SuperAdminService $admins = new SuperAdminService())
    {
    }

    public function showSetup(): never
    {
        if ($this->admins->hasAnyAdmin()) {
            $this->redirect('/super-admin/login');
        }

        $this->view('auth/super-admin/setup', ['title' => 'Platform Admin Setup']);
    }

    public function setup(Request $request): never
    {
        if ($this->admins->hasAnyAdmin()) {
            $this->redirect('/super-admin/login', 'Platform admin setup is already complete.', 'error');
        }

        $data = $request->all();
        Session::flashInput($data);

        if (trim((string) ($data['name'] ?? '')) === '' || trim((string) ($data['email'] ?? '')) === '' || trim((string) ($data['password'] ?? '')) === '') {
            $this->redirect('/super-admin/setup', 'Complete all platform admin fields.', 'error');
        }

        if (($data['password'] ?? '') !== ($data['password_confirmation'] ?? '')) {
            $this->redirect('/super-admin/setup', 'Password confirmation does not match.', 'error');
        }

        try {
            $admin = $this->admins->createFirstAdmin($data);
            Auth::login('super_admin', (int) $admin['id']);
        } catch (\Throwable $exception) {
            $this->redirect('/super-admin/setup', $exception->getMessage(), 'error');
        }

        $this->redirect('/super-admin/dashboard', 'Platform admin account created.');
    }

    public function showLogin(): never
    {
        if (!$this->admins->hasAnyAdmin()) {
            $this->redirect('/super-admin/setup');
        }

        $this->view('auth/super-admin/login', ['title' => 'Platform Admin Login']);
    }

    public function login(Request $request): never
    {
        if (!$this->admins->hasAnyAdmin()) {
            $this->redirect('/super-admin/setup');
        }

        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');
        Session::flashInput(['email' => $email]);

        if (!$this->admins->attemptLogin($email, $password)) {
            $this->redirect('/super-admin/login', 'Invalid platform admin credentials.', 'error');
        }

        $this->redirect('/super-admin/dashboard', 'Welcome back.');
    }

    public function logout(): never
    {
        Auth::logout();
        $this->redirect('/super-admin/login', 'You have been signed out.');
    }
}
