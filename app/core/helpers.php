<?php declare(strict_types=1);

namespace App\Core;

function base_url(string $path = ''): string
{
    $base = Env::get('APP_URL', '/');
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/** CSRF utilities */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}
function verify_csrf_post(): bool
{
    return isset($_POST['_token'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['_token']);
}

/** Simple auth helpers */
function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}
function auth_check(): bool
{
    return isset($_SESSION['user']);
}
