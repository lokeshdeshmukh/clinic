<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Services\AuthService;

final class ClinicAuthController extends Controller
{
    public function __construct(private readonly AuthService $auth = new AuthService())
    {
    }

    public function showRegister(): never
    {
        $this->view('auth/clinic/register', ['title' => 'Register Clinic']);
    }

    public function register(Request $request): never
    {
        $data = $request->all();
        Session::flashInput($data);

        $validator = new Validator();
        if (!$validator->validate($data, [
            'name' => ['required', 'min:3'],
            'address' => ['required', 'min:10'],
            'phone' => ['required', 'min:8'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ])) {
            $this->redirect('/clinic/register', 'Please complete all required clinic details.', 'error');
        }

        if (($data['password'] ?? '') !== ($data['password_confirmation'] ?? '')) {
            $this->redirect('/clinic/register', 'Password confirmation does not match.', 'error');
        }

        try {
            $this->auth->registerClinic($data, $request->file('logo'));
        } catch (\Throwable $exception) {
            $this->redirect('/clinic/register', $exception->getMessage(), 'error');
        }

        $this->redirect('/clinic/login', 'Registration complete. Check your email to verify the clinic account.');
    }

    public function showLogin(): never
    {
        $this->view('auth/clinic/login', ['title' => 'Clinic Admin Login']);
    }

    public function login(Request $request): never
    {
        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        try {
            $success = $this->auth->attemptClinicLogin($email, $password);
        } catch (\Throwable $exception) {
            $this->redirect('/clinic/login', $exception->getMessage(), 'error');
        }

        if (!$success) {
            $this->redirect('/clinic/login', 'Invalid login credentials.', 'error');
        }

        $this->redirect('/admin/dashboard', 'Welcome back.');
    }

    public function logout(): never
    {
        Auth::logout();
        $this->redirect('/clinic/login', 'You have been signed out.');
    }

    public function verify(Request $request): never
    {
        $token = (string) $request->query('token', '');
        if ($token === '' || !$this->auth->verifyClinic($token)) {
            $this->redirect('/clinic/login', 'Verification link is invalid or expired.', 'error');
        }

        $this->redirect('/clinic/login', 'Email verified. You can now sign in.');
    }

    public function showForgotPassword(): never
    {
        $this->view('auth/clinic/forgot-password', ['title' => 'Clinic Password Reset']);
    }

    public function sendResetLink(Request $request): never
    {
        $email = trim((string) $request->input('email'));
        if ($email !== '') {
            $this->auth->sendClinicResetLink($email);
        }

        $this->redirect('/clinic/login', 'If the account exists, a reset link has been sent.');
    }

    public function showResetPassword(Request $request): never
    {
        $this->view('auth/clinic/reset-password', [
            'title' => 'Reset Clinic Password',
            'token' => (string) $request->query('token', ''),
        ]);
    }

    public function resetPassword(Request $request): never
    {
        $token = (string) $request->input('token');
        $password = (string) $request->input('password');
        $confirmation = (string) $request->input('password_confirmation');

        if ($password === '' || $password !== $confirmation) {
            $this->redirect('/clinic/reset-password?token=' . urlencode($token), 'Password confirmation does not match.', 'error');
        }

        if (!$this->auth->resetClinicPassword($token, $password)) {
            $this->redirect('/clinic/reset-password?token=' . urlencode($token), 'Reset link is invalid or expired.', 'error');
        }

        $this->redirect('/clinic/login', 'Password updated. Please sign in.');
    }
}
