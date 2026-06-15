<?php

declare(strict_types=1);

use App\Controllers\AdminAppointmentController;
use App\Controllers\AvailabilityController;
use App\Controllers\BookingController;
use App\Controllers\ClinicAuthController;
use App\Controllers\ClinicDashboardController;
use App\Controllers\ClinicDirectoryController;
use App\Controllers\DeployController;
use App\Controllers\DoctorController;
use App\Controllers\PatientAuthController;
use App\Controllers\PatientDashboardController;
use App\Controllers\ReportsController;
use App\Controllers\SettingsController;
use App\Controllers\SuperAdminAuthController;
use App\Controllers\SuperAdminClinicController;

$router->get('/', [ClinicDirectoryController::class, 'home']);
$router->post('/deploy/run-updates', [DeployController::class, 'runUpdates']);

$router->get('/clinic/register', [ClinicAuthController::class, 'showRegister'], ['guest']);
$router->post('/clinic/register', [ClinicAuthController::class, 'register'], ['guest', 'csrf']);
$router->get('/clinic/login', [ClinicAuthController::class, 'showLogin'], ['guest']);
$router->post('/clinic/login', [ClinicAuthController::class, 'login'], ['guest', 'csrf']);
$router->post('/clinic/logout', [ClinicAuthController::class, 'logout'], ['auth:clinic', 'csrf']);
$router->get('/clinic/verify', [ClinicAuthController::class, 'verify']);
$router->get('/clinic/forgot-password', [ClinicAuthController::class, 'showForgotPassword'], ['guest']);
$router->post('/clinic/forgot-password', [ClinicAuthController::class, 'sendResetLink'], ['guest', 'csrf']);
$router->get('/clinic/reset-password', [ClinicAuthController::class, 'showResetPassword'], ['guest']);
$router->post('/clinic/reset-password', [ClinicAuthController::class, 'resetPassword'], ['guest', 'csrf']);

$router->get('/patient/register', [PatientAuthController::class, 'showRegister'], ['guest']);
$router->post('/patient/register', [PatientAuthController::class, 'register'], ['guest', 'csrf']);
$router->get('/patient/login', [PatientAuthController::class, 'showLogin'], ['guest']);
$router->post('/patient/login', [PatientAuthController::class, 'login'], ['guest', 'csrf']);
$router->post('/patient/login/otp/send', [PatientAuthController::class, 'sendOtp'], ['guest', 'csrf']);
$router->post('/patient/login/otp/verify', [PatientAuthController::class, 'verifyOtp'], ['guest', 'csrf']);
$router->post('/patient/login/google', [PatientAuthController::class, 'googleLogin'], ['guest', 'csrf']);
$router->post('/patient/logout', [PatientAuthController::class, 'logout'], ['auth:patient', 'csrf']);
$router->get('/patient/forgot-password', [PatientAuthController::class, 'showForgotPassword'], ['guest']);
$router->post('/patient/forgot-password', [PatientAuthController::class, 'sendResetLink'], ['guest', 'csrf']);
$router->get('/patient/reset-password', [PatientAuthController::class, 'showResetPassword'], ['guest']);
$router->post('/patient/reset-password', [PatientAuthController::class, 'resetPassword'], ['guest', 'csrf']);

$router->get('/super-admin/setup', [SuperAdminAuthController::class, 'showSetup'], ['guest']);
$router->post('/super-admin/setup', [SuperAdminAuthController::class, 'setup'], ['guest', 'csrf']);
$router->get('/super-admin/login', [SuperAdminAuthController::class, 'showLogin'], ['guest']);
$router->post('/super-admin/login', [SuperAdminAuthController::class, 'login'], ['guest', 'csrf']);
$router->post('/super-admin/logout', [SuperAdminAuthController::class, 'logout'], ['auth:super_admin', 'csrf']);

$router->get('/clinics', [ClinicDirectoryController::class, 'index']);
$router->get('/clinics/{slug}', [ClinicDirectoryController::class, 'showClinic']);
$router->get('/doctors/{id}', [ClinicDirectoryController::class, 'showDoctor']);
$router->get('/doctors/{id}/book', [BookingController::class, 'showBooking']);
$router->post('/doctors/{id}/book', [BookingController::class, 'book'], ['auth:patient', 'csrf']);

$router->get('/admin/dashboard', [ClinicDashboardController::class, 'index'], ['auth:clinic']);
$router->get('/admin/doctors', [DoctorController::class, 'index'], ['auth:clinic']);
$router->get('/admin/doctors/create', [DoctorController::class, 'create'], ['auth:clinic']);
$router->post('/admin/doctors', [DoctorController::class, 'store'], ['auth:clinic', 'csrf']);
$router->get('/admin/doctors/{id}/edit', [DoctorController::class, 'edit'], ['auth:clinic']);
$router->post('/admin/doctors/{id}/update', [DoctorController::class, 'update'], ['auth:clinic', 'csrf']);
$router->post('/admin/doctors/{id}/delete', [DoctorController::class, 'delete'], ['auth:clinic', 'csrf']);

$router->get('/admin/availability', [AvailabilityController::class, 'index'], ['auth:clinic']);
$router->post('/admin/availability', [AvailabilityController::class, 'store'], ['auth:clinic', 'csrf']);
$router->post('/admin/availability/{id}/delete', [AvailabilityController::class, 'delete'], ['auth:clinic', 'csrf']);

$router->get('/admin/appointments', [AdminAppointmentController::class, 'index'], ['auth:clinic']);
$router->post('/admin/appointments/{id}/cancel', [AdminAppointmentController::class, 'cancel'], ['auth:clinic', 'csrf']);
$router->post('/admin/appointments/{id}/reschedule', [AdminAppointmentController::class, 'reschedule'], ['auth:clinic', 'csrf']);
$router->post('/admin/appointments/{id}/complete', [AdminAppointmentController::class, 'complete'], ['auth:clinic', 'csrf']);

$router->get('/admin/reports', [ReportsController::class, 'index'], ['auth:clinic']);
$router->get('/admin/reports/export', [ReportsController::class, 'export'], ['auth:clinic']);
$router->get('/admin/settings', [SettingsController::class, 'edit'], ['auth:clinic']);
$router->post('/admin/settings', [SettingsController::class, 'update'], ['auth:clinic', 'csrf']);
$router->post('/admin/settings/doctor-hours', [SettingsController::class, 'storeDoctorHours'], ['auth:clinic', 'csrf']);
$router->post('/admin/settings/doctor-hours/{id}/delete', [SettingsController::class, 'deleteDoctorHours'], ['auth:clinic', 'csrf']);

$router->get('/patient/dashboard', [PatientDashboardController::class, 'index'], ['auth:patient']);
$router->post('/patient/appointments/{id}/cancel', [PatientDashboardController::class, 'cancel'], ['auth:patient', 'csrf']);
$router->post('/patient/appointments/{id}/reschedule', [PatientDashboardController::class, 'reschedule'], ['auth:patient', 'csrf']);

$router->get('/super-admin/dashboard', [SuperAdminClinicController::class, 'index'], ['auth:super_admin']);
$router->post('/super-admin/clinics', [SuperAdminClinicController::class, 'store'], ['auth:super_admin', 'csrf']);
