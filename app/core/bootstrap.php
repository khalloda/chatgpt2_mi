<?php declare(strict_types=1);

ini_set('display_errors', getenv('APP_DEBUG') === 'true' ? '1' : '0');
error_reporting(E_ALL);

date_default_timezone_set('UTC'); // adjust if needed

// simple PSR-ish autoloader with lowercase files
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (strpos($class, $prefix) === 0) {
        $relative = substr($class, strlen($prefix));
        $relative = str_replace('\\', '/', $relative);
        $path = __DIR__ . '/../' . strtolower($relative) . '.php';
        if (file_exists($path)) {
            require $path;
        }
    }
});

require __DIR__ . '/env.php';
require __DIR__ . '/helpers.php';
