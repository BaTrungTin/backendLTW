<?php

namespace App\Core;

class Response
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    public static function setCookie(string $name, string $value, int $maxAge): void
    {
        setcookie($name, $value, [
            'expires' => time() + $maxAge,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }

    public static function clearCookie(string $name): void
    {
        setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }
}
