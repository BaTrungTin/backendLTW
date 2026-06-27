<?php

namespace App\Core;

class Request
{
    public ?object $account = null;
    public ?array $permissions = null;
    public ?array $settingWebsiteInfo = null;

    public function __construct(
        public string $method,
        public string $path,
        public array $params = [],
    ) {}

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        $body = $this->body();
        if ($key === null) {
            return $body;
        }
        return $body[$key] ?? $default;
    }

    public function body(): array
    {
        static $parsed = null;
        if ($parsed !== null) {
            return $parsed;
        }
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $parsed = json_decode($raw, true) ?: [];
            return $parsed;
        }
        if ($this->method === 'POST' || $this->method === 'PATCH') {
            $parsed = array_merge($_POST, $_FILES ? ['_files' => $_FILES] : []);
            return $parsed;
        }
        $parsed = $_POST;
        return $parsed;
    }

    public function files(): array
    {
        return $_FILES;
    }

    public function cookie(string $key): ?string
    {
        return $_COOKIE[$key] ?? null;
    }
}
