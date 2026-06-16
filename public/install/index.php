<?php

declare(strict_types=1);

$basePath = dirname(__DIR__, 2);
require_once $basePath . '/app/Core/Env.php';
require_once $basePath . '/app/Core/InstallState.php';

$resolvedEnvPath = \App\Core\Env::resolvePath($basePath);
$preferredExternalEnvPath = dirname($basePath) . '/.clinicflow.env';
$envPath = is_file($resolvedEnvPath)
    ? $resolvedEnvPath
    : ((is_dir(dirname($preferredExternalEnvPath)) && is_writable(dirname($preferredExternalEnvPath)))
        ? $preferredExternalEnvPath
        : $basePath . '/.env');
$lockPath = \App\Core\InstallState::lockPath($basePath);
$templatePath = $basePath . '/.env.example';
$requestUriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/install/', PHP_URL_PATH) ?: '/install/';
$appBase = preg_replace('#/(?:install(?:/index\.php)?)$#', '', rtrim($requestUriPath, '/')) ?: '';
$appUrlGuess = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($appBase ?: '');

$errors = [];
$success = null;
$deployTokenValue = '';
$deployHookUrl = '';
$detectedInstalled = false;

if (!is_file($lockPath)) {
    $detectedInstalled = \App\Core\InstallState::isInstalled($basePath);
    if ($detectedInstalled) {
        \App\Core\InstallState::ensureLockFile($basePath);
    }
}

if (is_file($lockPath) || $detectedInstalled) {
    $success = 'ClinicFlow is already installed. Delete storage/installed.lock only if you intentionally want to reinstall.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $success === null) {
    $input = [
        'app_name' => trim((string) ($_POST['app_name'] ?? 'ClinicFlow')),
        'app_url' => trim((string) ($_POST['app_url'] ?? $appUrlGuess)),
        'app_timezone' => trim((string) ($_POST['app_timezone'] ?? 'Asia/Kolkata')),
        'session_name' => trim((string) ($_POST['session_name'] ?? 'clinicflow_session')),
        'deploy_token' => trim((string) ($_POST['deploy_token'] ?? '')),
        'db_host' => trim((string) ($_POST['db_host'] ?? 'localhost')),
        'db_port' => trim((string) ($_POST['db_port'] ?? '3306')),
        'db_name' => trim((string) ($_POST['db_name'] ?? '')),
        'db_user' => trim((string) ($_POST['db_user'] ?? '')),
        'db_pass' => (string) ($_POST['db_pass'] ?? ''),
        'mail_host' => trim((string) ($_POST['mail_host'] ?? '')),
        'mail_port' => trim((string) ($_POST['mail_port'] ?? '587')),
        'mail_username' => trim((string) ($_POST['mail_username'] ?? '')),
        'mail_password' => (string) ($_POST['mail_password'] ?? ''),
        'mail_encryption' => trim((string) ($_POST['mail_encryption'] ?? 'tls')),
        'mail_from_address' => trim((string) ($_POST['mail_from_address'] ?? '')),
        'mail_from_name' => trim((string) ($_POST['mail_from_name'] ?? ($_POST['app_name'] ?? 'ClinicFlow'))),
        'default_slot_duration' => trim((string) ($_POST['default_slot_duration'] ?? '30')),
        'upload_max_mb' => trim((string) ($_POST['upload_max_mb'] ?? '5')),
    ];

    foreach (['app_name', 'app_url', 'db_host', 'db_port', 'db_name', 'db_user', 'session_name'] as $required) {
        if ($input[$required] === '') {
            $errors[] = sprintf('%s is required.', str_replace('_', ' ', ucfirst($required)));
        }
    }

    if (!filter_var($input['app_url'], FILTER_VALIDATE_URL)) {
        $errors[] = 'App URL must be a valid URL.';
    }

    if (!is_writable(dirname($envPath)) && !is_file($envPath)) {
        $errors[] = 'The installer cannot write the environment file. Make the target directory writable and try again.';
    }

    if (!is_dir($basePath . '/storage/logs') && !mkdir($basePath . '/storage/logs', 0775, true) && !is_dir($basePath . '/storage/logs')) {
        $errors[] = 'Unable to create storage/logs.';
    }

    if (!is_dir($basePath . '/public/uploads') && !mkdir($basePath . '/public/uploads', 0775, true) && !is_dir($basePath . '/public/uploads')) {
        $errors[] = 'Unable to create public/uploads.';
    }

    if ($errors === []) {
        try {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $input['db_host'], $input['db_port'], $input['db_name']);
            $pdo = new PDO($dsn, $input['db_user'], $input['db_pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            $envTemplate = is_file($templatePath) ? (string) file_get_contents($templatePath) : '';
            if ($envTemplate === '') {
                throw new RuntimeException('.env.example is missing or unreadable.');
            }

            $replacements = [
                'APP_NAME' => $input['app_name'],
                'APP_URL' => rtrim($input['app_url'], '/'),
                'APP_TIMEZONE' => $input['app_timezone'],
                'APP_KEY' => bin2hex(random_bytes(32)),
                'SESSION_NAME' => $input['session_name'],
                'DEPLOY_TOKEN' => $input['deploy_token'] !== '' ? $input['deploy_token'] : bin2hex(random_bytes(24)),
                'DB_HOST' => $input['db_host'],
                'DB_PORT' => $input['db_port'],
                'DB_NAME' => $input['db_name'],
                'DB_USER' => $input['db_user'],
                'DB_PASS' => $input['db_pass'],
                'MAIL_HOST' => $input['mail_host'],
                'MAIL_PORT' => $input['mail_port'],
                'MAIL_USERNAME' => $input['mail_username'],
                'MAIL_PASSWORD' => $input['mail_password'],
                'MAIL_ENCRYPTION' => $input['mail_encryption'],
                'MAIL_FROM_ADDRESS' => $input['mail_from_address'],
                'MAIL_FROM_NAME' => $input['mail_from_name'],
                'DEFAULT_SLOT_DURATION' => $input['default_slot_duration'],
                'UPLOAD_MAX_MB' => $input['upload_max_mb'],
            ];

            $envContent = preg_replace_callback('/^([A-Z0-9_]+)=(.*)$/m', static function (array $matches) use ($replacements): string {
                $key = $matches[1];
                if (!array_key_exists($key, $replacements)) {
                    return $matches[0];
                }

                $value = (string) $replacements[$key];
                $quoted = '"' . str_replace('"', '\"', $value) . '"';
                return $key . '=' . $quoted;
            }, $envTemplate);

            if ($envContent === null || file_put_contents($envPath, $envContent) === false) {
                throw new RuntimeException('Unable to write the environment file.');
            }

            require_once $basePath . '/bootstrap/app.php';
            $migrationResult = (new \App\Services\MigrationService())->runPending();

            if (file_put_contents($lockPath, 'Installed at ' . date('c')) === false) {
                throw new RuntimeException('Unable to write storage/installed.lock.');
            }

            $deployTokenValue = (string) $replacements['DEPLOY_TOKEN'];
            $deployHookUrl = rtrim($input['app_url'], '/') . '/deploy/run-updates?token=' . urlencode($deployTokenValue);
            $success = 'Installation completed successfully. You can now sign in to the platform admin and create clinics from there.';
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}

function installerOld(string $key, string $default = ''): string
{
    return htmlspecialchars((string) ($_POST[$key] ?? $default), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClinicFlow Installer</title>
    <style>
        body{margin:0;font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a}
        .wrap{max-width:960px;margin:0 auto;padding:32px 16px 48px}
        .hero{background:linear-gradient(135deg,#0f172a,#1d4ed8);color:#fff;border-radius:24px;padding:28px}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:24px;padding:24px;margin-top:20px;box-shadow:0 20px 40px -24px rgba(15,23,42,.25)}
        .grid{display:grid;gap:16px}
        .cols-2{grid-template-columns:repeat(auto-fit,minmax(220px,1fr))}
        label{display:block;font-size:14px;font-weight:700;margin-bottom:8px}
        input,select{width:100%;padding:12px 14px;border:1px solid #cbd5e1;border-radius:14px;font-size:14px;box-sizing:border-box}
        .btn{background:#2563eb;color:#fff;border:none;border-radius:16px;padding:14px 18px;font-weight:700;font-size:15px;cursor:pointer}
        .note{font-size:14px;color:#475569;line-height:1.6}
        .error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:18px;padding:14px 16px;margin-bottom:16px}
        .success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:18px;padding:14px 16px;margin-bottom:16px}
        ul{margin:0;padding-left:18px}
        a{color:#1d4ed8;text-decoration:none}
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <p style="margin:0 0 8px;font-size:13px;letter-spacing:.18em;text-transform:uppercase;color:#bfdbfe;">Shared Hosting Setup</p>
        <h1 style="margin:0;font-size:34px;line-height:1.15;">ClinicFlow browser installer</h1>
        <p style="margin:14px 0 0;color:#dbeafe;max-width:760px;">Create your MySQL database from the hosting panel first, then fill this form. No terminal access is required.</p>
    </div>

    <div class="card">
        <?php if ($errors !== []): ?>
            <div class="error">
                <strong>Installation could not continue:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success !== null): ?>
            <div class="success">
                <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                <?php if ($deployTokenValue !== ''): ?>
                    <div style="margin-top:12px;font-size:14px;line-height:1.6;">
                        <strong>Deploy token:</strong> <?= htmlspecialchars($deployTokenValue, ENT_QUOTES, 'UTF-8') ?><br>
                        <strong>GitHub secret `DEPLOY_HOOK_URL`:</strong><br>
                        <span style="word-break:break-all;"><?= htmlspecialchars($deployHookUrl, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endif; ?>
                <div style="margin-top:10px;">
                    <a href="<?= htmlspecialchars(($appBase ?: '') . '/super-admin/login', ENT_QUOTES, 'UTF-8') ?>">Open platform admin login</a>
                    |
                    <a href="<?= htmlspecialchars(($appBase ?: '') . '/clinic/register', ENT_QUOTES, 'UTF-8') ?>">Open clinic registration</a>
                </div>
            </div>
        <?php endif; ?>

        <p class="note">Recommended flow: create a new subdomain, upload the ZIP into that subdomain folder, extract it, then open <strong>/install/</strong> on the subdomain. When possible, the installer will save your live configuration in an external <strong>.clinicflow.env</strong> file one level above the deployed app so Hostinger Git deploy will not overwrite it.</p>

        <form method="post" class="grid" action="">
            <div class="grid cols-2">
                <div>
                    <label for="app_name">App name</label>
                    <input id="app_name" name="app_name" value="<?= installerOld('app_name', 'ClinicFlow') ?>" required>
                </div>
                <div>
                    <label for="app_url">App URL</label>
                    <input id="app_url" name="app_url" value="<?= installerOld('app_url', $appUrlGuess) ?>" required>
                </div>
            </div>

            <div class="grid cols-2">
                <div>
                    <label for="app_timezone">Timezone</label>
                    <input id="app_timezone" name="app_timezone" value="<?= installerOld('app_timezone', 'Asia/Kolkata') ?>" required>
                </div>
                <div>
                    <label for="session_name">Session name</label>
                    <input id="session_name" name="session_name" value="<?= installerOld('session_name', 'clinicflow_session') ?>" required>
                </div>
            </div>

            <div>
                <label for="deploy_token">Deploy token</label>
                <input id="deploy_token" name="deploy_token" value="<?= installerOld('deploy_token') ?>" placeholder="Leave blank to auto-generate">
            </div>

            <div class="grid cols-2">
                <div>
                    <label for="db_host">Database host</label>
                    <input id="db_host" name="db_host" value="<?= installerOld('db_host', 'localhost') ?>" required>
                </div>
                <div>
                    <label for="db_port">Database port</label>
                    <input id="db_port" name="db_port" value="<?= installerOld('db_port', '3306') ?>" required>
                </div>
            </div>

            <div class="grid cols-2">
                <div>
                    <label for="db_name">Database name</label>
                    <input id="db_name" name="db_name" value="<?= installerOld('db_name') ?>" required>
                </div>
                <div>
                    <label for="db_user">Database username</label>
                    <input id="db_user" name="db_user" value="<?= installerOld('db_user') ?>" required>
                </div>
            </div>

            <div>
                <label for="db_pass">Database password</label>
                <input id="db_pass" type="password" name="db_pass" value="<?= installerOld('db_pass') ?>">
            </div>

            <div class="grid cols-2">
                <div>
                    <label for="mail_host">SMTP host</label>
                    <input id="mail_host" name="mail_host" value="<?= installerOld('mail_host') ?>">
                </div>
                <div>
                    <label for="mail_port">SMTP port</label>
                    <input id="mail_port" name="mail_port" value="<?= installerOld('mail_port', '587') ?>">
                </div>
            </div>

            <div class="grid cols-2">
                <div>
                    <label for="mail_username">SMTP username</label>
                    <input id="mail_username" name="mail_username" value="<?= installerOld('mail_username') ?>">
                </div>
                <div>
                    <label for="mail_password">SMTP password</label>
                    <input id="mail_password" type="password" name="mail_password" value="<?= installerOld('mail_password') ?>">
                </div>
            </div>

            <div class="grid cols-2">
                <div>
                    <label for="mail_encryption">SMTP encryption</label>
                    <select id="mail_encryption" name="mail_encryption">
                        <option value="tls" <?= installerOld('mail_encryption', 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                        <option value="ssl" <?= installerOld('mail_encryption') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="" <?= installerOld('mail_encryption') === '' ? 'selected' : '' ?>>None</option>
                    </select>
                </div>
                <div>
                    <label for="mail_from_address">From email</label>
                    <input id="mail_from_address" name="mail_from_address" value="<?= installerOld('mail_from_address') ?>">
                </div>
            </div>

            <div class="grid cols-2">
                <div>
                    <label for="mail_from_name">From name</label>
                    <input id="mail_from_name" name="mail_from_name" value="<?= installerOld('mail_from_name', 'ClinicFlow') ?>">
                </div>
                <div>
                    <label for="default_slot_duration">Default slot duration (minutes)</label>
                    <input id="default_slot_duration" name="default_slot_duration" value="<?= installerOld('default_slot_duration', '30') ?>">
                </div>
            </div>

            <div>
                <label for="upload_max_mb">Upload max size (MB)</label>
                <input id="upload_max_mb" name="upload_max_mb" value="<?= installerOld('upload_max_mb', '5') ?>">
            </div>

            <button class="btn" type="submit">Install ClinicFlow</button>
        </form>
    </div>
</div>
</body>
</html>
