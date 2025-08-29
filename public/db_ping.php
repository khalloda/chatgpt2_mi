<?php declare(strict_types=1);
// TEMPORARY DIAGNOSTIC. DELETE AFTER USE.
// Visit as: /db_ping.php?key=letmein123  (change the key below)

$key = 'letmein123';
if (!isset($_GET['key']) || $_GET['key'] !== $key) {
    http_response_code(403);
    exit('Forbidden');
}

require __DIR__ . '/../app/core/bootstrap.php';

use App\Core\DB;

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = DB::conn();
    echo "Connected.\n";
    $val = $pdo->query('SELECT 1')->fetchColumn();
    echo "SELECT 1 => {$val}\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
