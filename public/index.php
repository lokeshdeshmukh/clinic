<?php

declare(strict_types=1);

$envFile = dirname(__DIR__) . '/.env';
$installLock = dirname(__DIR__) . '/storage/installed.lock';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$basePath = $basePath === '/' ? '' : $basePath;
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$installPath = $basePath . '/install/';
$normalizedInstallPath = rtrim($installPath, '/');

if ((!is_file($envFile) || !is_file($installLock)) && !str_starts_with($requestPath, $normalizedInstallPath)) {
    header('Location: ' . ($installPath ?: '/install/'));
    exit;
}

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->run();
