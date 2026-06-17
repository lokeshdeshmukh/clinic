<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private bool $jsonLoaded = false;
    private array $jsonPayload = [];

    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $input,
        public readonly array $files,
        public readonly array $server
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_POST['_method'] ?? $_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $input = $method === 'GET' ? $_GET : $_POST;

        return new self(
            $method,
            rtrim($path, '/') ?: '/',
            $_GET,
            $input,
            $_FILES,
            $_SERVER
        );
    }

    public function isJson(): bool
    {
        $accept = $this->server['HTTP_ACCEPT'] ?? '';
        $contentType = $this->server['CONTENT_TYPE'] ?? '';

        return str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');
    }

    public function all(): array
    {
        if ($this->isJson()) {
            if (!$this->jsonLoaded) {
                $decoded = json_decode((string) file_get_contents('php://input'), true);
                $this->jsonPayload = is_array($decoded) ? $decoded : [];
                $this->jsonLoaded = true;
            }

            return $this->jsonPayload;
        }

        return $this->input;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $data = $this->all();

        return $data[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }
}
