<?php

declare(strict_types=1);

$baseRoot = dirname(__DIR__);
require_once $baseRoot . '/app/Core/Env.php';
require_once $baseRoot . '/app/Core/InstallState.php';

$envFile = \App\Core\Env::resolvePath($baseRoot);
$installLock = \App\Core\InstallState::lockPath($baseRoot);
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$basePath = $basePath === '/' ? '' : $basePath;
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$installPath = $basePath . '/install/';
$normalizedInstallPath = rtrim($installPath, '/');
$isInstalled = is_file($installLock);

if (!$isInstalled && is_file($envFile) && \App\Core\InstallState::isInstalled($baseRoot)) {
    $isInstalled = true;
    \App\Core\InstallState::ensureLockFile($baseRoot);
}

if ((!is_file($envFile) || !$isInstalled) && !str_starts_with($requestPath, $normalizedInstallPath)) {
    header('Location: ' . ($installPath ?: '/install/'));
    exit;
}

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->run();
