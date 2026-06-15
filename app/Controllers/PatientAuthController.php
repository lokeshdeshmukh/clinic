<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Services\AuthService;

final class PatientAuthController extends Controller
{
    public function __construct(private readonly AuthService $auth = new AuthService())
    {
    }

    public function showRegister(): never
    {
        $this->view('auth/patient/register', ['title' => 'Patient Registration']);
    }

    public function register(Request $request): never
    {
        $data = $request->all();
        Session::flashInput($data);

        $validator = new Validator();
        if (!$validator->validate($data, [
            'first_name' => ['required', 'min:2'],
            'last_name' => ['required', 'min:2'],
            'phone' => ['required', 'min:8'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ])) {
            $this->redirect('/patient/register', 'Please complete all required patient details.', 'error');
        }

        if (($data['password'] ?? '') !== ($data['password_confirmation'] ?? '')) {
            $this->redirect('/patient/register', 'Password confirmation does not match.', 'error');
        }

        try {
            $patient = $this->auth->registerPatient($data);
            Auth::login('patient', (int) $patient['id']);
        } catch (\Throwable $exception) {
            $this->redirect('/patient/register', $exception->getMessage(), 'error');
        }

        $this->redirect('/patient/dashboard', 'Your account is ready.');
    }

    public function showLogin(): never
    {
        $this->view('auth/patient/login', ['title' => 'Patient Login']);
    }

    public function login(Request $request): never
    {
        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        if (!$this->auth->attemptPatientLogin($email, $password)) {
            $this->redirect('/patient/login', 'Invalid login credentials.', 'error');
        }

        $this->redirect('/patient/dashboard', 'Welcome back.');
    }

    public function logout(): never
    {
        Auth::logout();
        $this->redirect('/patient/login', 'You have been signed out.');
    }

    public function showForgotPassword(): never
    {
        $this->view('auth/patient/forgot-password', ['title' => 'Patient Password Reset']);
    }

    public function sendResetLink(Request $request): never
    {
        $email = trim((string) $request->input('email'));
        if ($email !== '') {
            $this->auth->sendPatientResetLink($email);
        }

        $this->redirect('/patient/login', 'If the account exists, a reset link has been sent.');
    }

    public function showResetPassword(Request $request): never
    {
        $this->view('auth/patient/reset-password', [
            'title' => 'Reset Patient Password',
            'token' => (string) $request->query('token', ''),
        ]);
    }

    public function resetPassword(Request $request): never
    {
        $token = (string) $request->input('token');
        $password = (string) $request->input('password');
        $confirmation = (string) $request->input('password_confirmation');

        if ($password === '' || $password !== $confirmation) {
            $this->redirect('/patient/reset-password?token=' . urlencode($token), 'Password confirmation does not match.', 'error');
        }

        if (!$this->auth->resetPatientPassword($token, $password)) {
            $this->redirect('/patient/reset-password?token=' . urlencode($token), 'Reset link is invalid or expired.', 'error');
        }

        $this->redirect('/patient/login', 'Password updated. Please sign in.');
    }
}
