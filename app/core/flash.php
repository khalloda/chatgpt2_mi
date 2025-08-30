<?php declare(strict_types=1);

namespace App\Core;

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function flash_get(string $type): ?string
{
    if (!isset($_SESSION['flash'][$type])) return null;
    $msg = $_SESSION['flash'][$type];
    unset($_SESSION['flash'][$type]);
    return $msg;
}

function has_flash(string $type): bool
{
    return isset($_SESSION['flash'][$type]);
}



function require_auth(): void
{
    if (!auth_check()) {
        flash_set('error', 'Please log in to continue.');
        redirect('/login');
    }
}
