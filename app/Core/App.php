<?php

declare(strict_types=1);

namespace App\Core;

use App\Services\MigrationService;
use Throwable;

final class App
{
    public function run(): void
    {
        $this->runPendingMigrationsIfEnabled();

        $router = new Router();
        require base_path('routes/web.php');
        require base_path('routes/api.php');

        try {
            $router->dispatch(Request::capture());
        } catch (Throwable $exception) {
            $this->report($exception);

            if (config('app.debug', false)) {
                Response::abort(500, nl2br(e($exception->getMessage() . "\n" . $exception->getTraceAsString())));
            }

            Response::abort(500, 'Something went wrong. Please try again.');
        }
    }

    private function runPendingMigrationsIfEnabled(): void
    {
        $enabled = filter_var((string) env('AUTO_RUN_MIGRATIONS', 'true'), FILTER_VALIDATE_BOOL);
        if ($enabled === false) {
            return;
        }

        try {
            (new MigrationService())->runPending();
        } catch (Throwable $exception) {
            $this->report($exception);

            if (config('app.debug', false)) {
                throw $exception;
            }
        }
    }

    private function report(Throwable $exception): void
    {
        $line = sprintf(
            "[%s] %s in %s:%d%s",
            date('c'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            PHP_EOL
        );

        file_put_contents(storage_path('logs/app.log'), $line, FILE_APPEND);
    }
}
