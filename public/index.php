<?php

require dirname(__DIR__) . '/bootstrap.php';

/** @var \App\Core\Router $router */
$router = require dirname(__DIR__) . '/routes/web.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

// Support method override for PATCH/DELETE from forms
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

$router->dispatch($method, $uri);
