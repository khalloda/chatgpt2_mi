<?php declare(strict_types=1);

// PSR-ish autoloader (lowercase files)
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

use App\Core\Env;

// timezone & error display from .env
date_default_timezone_set(Env::get('APP_TIMEZONE', 'UTC'));
$debug = Env::get('APP_DEBUG', 'false') === 'true';
ini_set('display_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

// secure session
if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
