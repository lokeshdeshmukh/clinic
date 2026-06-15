<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/app'): string
    {
        $viewFile = view_path($view . '.php');

        if (!is_file($viewFile)) {
            throw new \RuntimeException("View [$view] not found.");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = (string) ob_get_clean();

        if ($layout === null) {
            return $content;
        }

        $layoutFile = view_path($layout . '.php');
        if (!is_file($layoutFile)) {
            throw new \RuntimeException("Layout [$layout] not found.");
        }

        ob_start();
        require $layoutFile;

        return (string) ob_get_clean();
    }
}
