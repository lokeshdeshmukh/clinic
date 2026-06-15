<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/helpers.php';

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        if (!str_starts_with($class, 'PHPMailer\\PHPMailer\\')) {
            return;
        }

        $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen('PHPMailer\\PHPMailer\\')));
        $file = BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'ThirdParty' . DIRECTORY_SEPARATOR . 'PHPMailer' . DIRECTORY_SEPARATOR . 'PHPMailer' . DIRECTORY_SEPARATOR . $relative . '.php';

        if (is_file($file)) {
            require $file;
        }

        return;
    }

    $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 4));
    $file = BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $relative . '.php';

    if (is_file($file)) {
        require $file;
    }
});

if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

\App\Core\Env::load(\App\Core\Env::resolvePath(BASE_PATH));
\App\Core\Config::bootstrap(BASE_PATH . '/config');
\App\Core\Session::start((string) config('app.session_name', 'clinicflow_session'));

date_default_timezone_set((string) config('app.timezone', 'UTC'));

return new \App\Core\App();
