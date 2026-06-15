<?php

declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static array $items = [];
    private static string $configPath = '';

    public static function bootstrap(string $configPath): void
    {
        self::$configPath = rtrim($configPath, DIRECTORY_SEPARATOR);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        [$file, $nested] = array_pad(explode('.', $key, 2), 2, null);

        if (!array_key_exists($file, self::$items)) {
            $path = self::$configPath . DIRECTORY_SEPARATOR . $file . '.php';
            self::$items[$file] = is_file($path) ? require $path : [];
        }

        $value = self::$items[$file];

        if ($nested === null) {
            return $value;
        }

        foreach (explode('.', $nested) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
