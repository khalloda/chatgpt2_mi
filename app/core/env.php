<?php declare(strict_types=1);

namespace App\Core;

final class Env
{
    private static ?array $cache = null;

    public static function get(string $key, ?string $default = null): ?string
    {
        if (self::$cache === null) {
            $file = __DIR__ . '/../../config/.env';
            self::$cache = file_exists($file)
                ? parse_ini_file($file, false, INI_SCANNER_TYPED) ?: []
                : [];
        }
        return isset(self::$cache[$key]) ? (string) self::$cache[$key] : $default;
    }
}

function env(string $key, ?string $default = null): ?string
{
    return Env::get($key, $default);
}
