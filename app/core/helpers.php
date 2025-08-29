<?php declare(strict_types=1);

namespace App\Core;

function base_url(string $path = ''): string
{
    $base = Env::get('APP_URL', '/');
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}
