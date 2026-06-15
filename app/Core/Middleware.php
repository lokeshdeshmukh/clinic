<?php

declare(strict_types=1);

namespace App\Core;

final class Middleware
{
    public static function handle(string $name, Request $request): void
    {
        if ($name === 'guest') {
            if (Auth::check()) {
                $redirect = match (Auth::guard()) {
                    'clinic' => '/admin/dashboard',
                    'super_admin' => '/super-admin/dashboard',
                    default => '/patient/dashboard',
                };
                Response::redirect($redirect);
            }

            return;
        }

        if ($name === 'auth:clinic') {
            if (!Auth::check('clinic')) {
                Session::flash('error', 'Please sign in as a clinic admin to continue.');
                Response::redirect('/clinic/login');
            }

            return;
        }

        if ($name === 'auth:patient') {
            if (!Auth::check('patient')) {
                Session::flash('error', 'Please sign in as a patient to continue.');
                Response::redirect('/patient/login');
            }

            return;
        }

        if ($name === 'auth:super_admin') {
            if (!Auth::check('super_admin')) {
                Session::flash('error', 'Please sign in as a platform admin to continue.');
                Response::redirect('/super-admin/login');
            }

            return;
        }

        if ($name === 'csrf' && in_array($request->method, ['POST', 'PUT', 'DELETE'], true)) {
            if (!Csrf::validate((string) $request->input('_token'))) {
                Response::abort(419, 'Invalid or expired CSRF token.');
            }
        }
    }
}
