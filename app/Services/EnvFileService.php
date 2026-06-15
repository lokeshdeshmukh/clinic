<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class EnvFileService
{
    public function set(array $values): void
    {
        $path = \App\Core\Env::resolvePath(base_path());
        $content = is_file($path) ? file_get_contents($path) : file_get_contents(base_path('.env.example'));

        if ($content === false || $content === '') {
            throw new RuntimeException('Unable to read the environment file.');
        }

        foreach ($values as $key => $value) {
            $quoted = '"' . str_replace('"', '\"', (string) $value) . '"';
            $pattern = '/^' . preg_quote((string) $key, '/') . '=(.*)$/m';

            if (preg_match($pattern, $content) === 1) {
                $content = (string) preg_replace($pattern, $key . '=' . $quoted, $content);
                continue;
            }

            $content = rtrim($content) . PHP_EOL . $key . '=' . $quoted . PHP_EOL;
        }

        if (file_put_contents($path, $content) === false) {
            throw new RuntimeException('Unable to write the environment file.');
        }
    }
}
