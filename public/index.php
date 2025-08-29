<?php declare(strict_types=1);

require __DIR__ . '/../app/core/bootstrap.php';

use App\Core\Router;

$router = new Router();

// basic routes
$router->get('/', 'homecontroller@index');
$router->get('/health', function () {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'OK';
});

// 404 handler happens in dispatch if no match
$router->dispatch();
