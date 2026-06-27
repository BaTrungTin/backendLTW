<?php

namespace App\Core;

class View
{
    private static array $globals = [];

    public static function share(string $key, mixed $value): void
    {
        self::$globals[$key] = $value;
    }

    public static function render(string $name, array $data = []): void
    {
        $merged = array_merge(self::$globals, $data);
        $merged['pathAdmin'] = $GLOBALS['pathAdmin'] ?? 'admin';

        $phpPath = BASE_PATH . '/views/' . str_replace('.', '/', $name) . '.php';
        if (!is_file($phpPath)) {
            $phpPath = BASE_PATH . '/views/' . $name . '.php';
        }

        if (is_file($phpPath)) {
            extract($merged, EXTR_SKIP);
            include $phpPath;
            return;
        }

        $pugPath = BASE_PATH . '/views/' . str_replace('.', '/', $name) . '.pug';
        if (!is_file($pugPath)) {
            $pugPath = BASE_PATH . '/views/' . $name . '.pug';
        }

        if (is_file($pugPath)) {
            self::renderPug($pugPath, $merged);
            return;
        }

        http_response_code(500);
        echo "View not found: {$name}";
    }

    private static function renderPug(string $pugPath, array $data): void
    {
        $script = BASE_PATH . '/scripts/render-pug.mjs';

        // Ghi data ra file tạm để tránh giới hạn 8192 bytes trên Windows
        $tempFile = tempnam(sys_get_temp_dir(), 'pug_');

        file_put_contents(
            $tempFile,
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );

        $cmd = sprintf(
            'node %s %s %s 2>&1',
            escapeshellarg($script),
            escapeshellarg($pugPath),
            escapeshellarg($tempFile)
        );

        $output = shell_exec($cmd);

        @unlink($tempFile);

        if ($output === null) {
            http_response_code(500);
            echo 'View render error. Ensure Node.js is installed and available in PATH.';
            return;
        }

        echo $output;
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars(
            (string) ($value ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}