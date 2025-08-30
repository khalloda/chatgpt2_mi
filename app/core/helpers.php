<?php declare(strict_types=1);

namespace App\Core;

function base_url(string $path = ''): string
{
    $base = Env::get('APP_URL', '/');
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function format_note_html(string $text): string {
    // 1) escape HTML
    $safe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    // 2) linkify http/https URLs (very conservative pattern)
    $safe = preg_replace(
        '~(https?://[^\s<]+)~i',
        '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
        $safe
    );

    // 3) newlines -> <br>
    return nl2br($safe, false);
}

/**
 * Build a URL to current path with a replaced/added query param.
 * Minimal helper for pagination/checkbox toggles.
 */
function url_with_query(array $pairs): string {
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    $parts = parse_url($uri);
    $path = $parts['path'] ?? '/';
    parse_str($parts['query'] ?? '', $q);
    foreach ($pairs as $k => $v) {
        if ($v === null) unset($q[$k]); else $q[$k] = $v;
    }
    $qs = http_build_query($q);
    return $path . ($qs ? ('?' . $qs) : '');
}
function redirect(string $to): void
{
    // If already absolute (http/https), use as-is
    if (preg_match('~^https?://~i', $to)) {
        header('Location: ' . $to);
    } else {
        header('Location: ' . base_url($to));
    }
    exit;
}
function activity_log(string $action, string $entity_type, int $entity_id, array $meta = []): void
{
    try {
        if (!isset($_SESSION)) { session_start(); }
        $actor = $_SESSION['user']['email'] ?? 'system';
        $pdo = DB::conn();
        $st = $pdo->prepare("INSERT INTO activity_log (actor, action, entity_type, entity_id, meta)
                             VALUES (?,?,?,?,?)");
        $st->execute([$actor, $action, $entity_type, $entity_id, json_encode($meta, JSON_UNESCAPED_UNICODE)]);
    } catch (\Throwable $e) {
        // logging is best-effort; ignore failures
    }
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
