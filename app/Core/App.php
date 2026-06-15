<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class App
{
    public function run(): void
    {
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
