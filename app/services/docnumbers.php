<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use PDO;

final class DocNumbers
{
    /** Create/ensure the sequences table */
    private static function ensureTable(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS doc_sequences (
              prefix VARCHAR(10) NOT NULL,
              y INT NOT NULL,
              last_no INT NOT NULL DEFAULT 0,
              PRIMARY KEY (prefix, y)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    /** Format like PO2025-0007 */
    private static function fmt(string $prefix, int $year, int $n): string
    {
        return sprintf('%s%04d-%04d', strtoupper($prefix), $year, $n);
    }

    /**
     * Returns the next number WITHOUT incrementing the counter.
     * Safe for previews; concurrent saves may still advance it.
     */
    public static function peek(string $prefix): string
    {
        $pdo  = DB::conn();
        self::ensureTable($pdo);

        $year = (int)date('Y');
        $st = $pdo->prepare("SELECT last_no FROM doc_sequences WHERE prefix=? AND y=?");
        $st->execute([$prefix, $year]);
        $last = (int)($st->fetchColumn() ?: 0);

        return self::fmt($prefix, $year, $last + 1);
    }

    /**
     * Returns the next number AND increments the counter atomically.
     * Retries once if a unique constraint race occurs on the target table.
     */
    public static function next(string $prefix): string
    {
        $pdo  = DB::conn();
        self::ensureTable($pdo);

        $year = (int)date('Y');

        $pdo->beginTransaction();
        try {
            // lock row
            $sel = $pdo->prepare("SELECT last_no FROM doc_sequences WHERE prefix = ? AND y = ? FOR UPDATE");
            $sel->execute([$prefix, $year]);
            $row = $sel->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $next = (int)$row['last_no'] + 1;
                $upd  = $pdo->prepare("UPDATE doc_sequences SET last_no = ? WHERE prefix = ? AND y = ?");
                $upd->execute([$next, $prefix, $year]);
            } else {
                $next = 1;
                $ins  = $pdo->prepare("INSERT INTO doc_sequences(prefix, y, last_no) VALUES(?,?,?)");
                $ins->execute([$prefix, $year, $next]);
            }

            $pdo->commit();
            return self::fmt($prefix, $year, $next);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
