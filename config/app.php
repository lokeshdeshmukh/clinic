<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'ClinicFlow'),
    'env' => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'key' => env('APP_KEY', ''),
    'session_name' => env('SESSION_NAME', 'clinicflow_session'),
    'default_slot_duration' => (int) env('DEFAULT_SLOT_DURATION', 30),
    'upload_max_mb' => (int) env('UPLOAD_MAX_MB', 5),
];
