<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

require BASE_PATH . '/vendor/autoload.php';

$envFile = BASE_PATH . '/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

$config = require BASE_PATH . '/config/app.php';
$dbConfig = require BASE_PATH . '/config/database.php';
$variables = require BASE_PATH . '/config/variables.php';

App\Core\Database::init($dbConfig);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$GLOBALS['config'] = $config;
$GLOBALS['variables'] = $variables;
$GLOBALS['pathAdmin'] = $config['path_admin'];

if (!is_dir($config['upload_dir'])) {
    mkdir($config['upload_dir'], 0755, true);
}
