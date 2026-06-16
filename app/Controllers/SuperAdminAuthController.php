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
        $this->redirect('/super-admin/login');
    }

    public function setup(Request $request): never
    {
        $this->redirect('/super-admin/login', 'Platform admin access is managed through the fixed super admin credentials.', 'error');
    }

    public function showLogin(): never
    {
        $this->admins->ensureDefaultAdmin();

        $this->view('auth/super-admin/login', ['title' => 'Platform Admin Login']);
    }

    public function login(Request $request): never
    {
        $this->admins->ensureDefaultAdmin();

        $identifier = trim((string) $request->input('identifier'));
        $password = (string) $request->input('password');
        Session::flashInput(['identifier' => $identifier]);

        if (!$this->admins->attemptLogin($identifier, $password)) {
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
