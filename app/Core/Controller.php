<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/app'): never
    {
        echo View::render($view, $data, $layout);
        exit;
    }

    protected function json(array $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    protected function redirect(string $path, ?string $message = null, string $level = 'success'): never
    {
        if ($message !== null) {
            Session::flash($level, $message);
        }

        Response::redirect($path);
    }
}
