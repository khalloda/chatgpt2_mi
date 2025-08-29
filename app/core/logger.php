<?php declare(strict_types=1);

namespace App\Core;

final class Logger
{
    public static function log(string $level, string $message, array $context = []): void
    {
        $dir = __DIR__ . '/../../storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $line = sprintf(
            "%s %s %s %s%s",
            date('c'),
            strtoupper($level),
            $message,
            $context ? json_encode($context, JSON_UNESCAPED_SLASHES) : '',
            PHP_EOL
        );
        @file_put_contents($dir . '/app.log', $line, FILE_APPEND | LOCK_EX);
    }
    public static function info(string $message, array $context = []): void  { self::log('info',  $message, $context); }
    public static function error(string $message, array $context = []): void { self::log('error', $message, $context); }
}
