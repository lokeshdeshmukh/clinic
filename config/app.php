<?php

declare(strict_types=1);

$buildMetaPath = base_path('storage/app_version.json');
$buildMeta = [
    'version' => '0.0.0.1',
    'commit' => 'manual',
    'deployed_at' => null,
    'source' => 'zip-upload',
];

if (is_file($buildMetaPath)) {
    $decoded = json_decode((string) file_get_contents($buildMetaPath), true);
    if (is_array($decoded)) {
        $buildMeta = array_merge($buildMeta, $decoded);
    }
}

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
    'build' => $buildMeta,
];
