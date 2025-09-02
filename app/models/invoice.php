<?php declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class Invoice
{
    /** Next/peek are optional; keep here if you already use centralized numbering */
    public static function nextNumber(): string {
        return \App\Services\DocNumbers::next('inv');
    }
    public static function peekNumber(): string {
        return \App\Services\DocNumbers::peek('inv');
    }

    public static function all(): array
    {
        $sql = "SELECT i.*, c.name AS customer_name
                  FROM invoices i
             LEFT JOIN customers c ON c.id = i.customer_id
              ORDER BY i.id DESC";
        return DB::conn()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function find(int $id): ?array
    {
        $st = DB::conn()->prepare("SELECT * FROM invoices WHERE id=?");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function items(int $invoiceId): array
    {
        $pdo = DB::conn();
        [$tbl, $hasWh, $hasLineTotal] = self::detectItemsMeta($pdo);

        // Build select list dynamically
        $select = "il.product_id, il.qty, il.price";
        if ($hasLineTotal) {
            $select .= ", il.line_total";
        } else {
            $select .= ", (il.qty * il.price) AS line_total";
        }
        if ($hasWh) {
            $select .= ", il.warehouse_id, w.name AS warehouse_name";
        }

        $sql =
            "SELECT {$select}, p.code AS product_code, p.name AS product_name
               FROM {$tbl} il
               JOIN products p ON p.id = il.product_id " .
            ($hasWh ? "LEFT JOIN warehouses w ON w.id = il.warehouse_id " : "") .
            "WHERE il.invoice_id = ?
             ORDER BY il.id";

        $st = $pdo->prepare($sql);
        $st->execute([$invoiceId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function recomputePaidAndStatus(int $id): void
    {
        $pdo = DB::conn();
        $st = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM invoice_payments WHERE invoice_id=?");
        $st->execute([$id]); $paid = (float)$st->fetchColumn();

        $st2 = $pdo->prepare("SELECT total FROM invoices WHERE id=?");
        $st2->execute([$id]); $total = (float)$st2->fetchColumn();

        $status = ($paid <= 0.0) ? 'unpaid' : (($paid + 0.00001 < $total) ? 'partial' : 'paid');
        $upd = $pdo->prepare("UPDATE invoices SET paid_amount=?, status=? WHERE id=?");
        $upd->execute([$paid, $status, $id]);
    }

    /* --------- helpers --------- */

    private static function detectItemsMeta(PDO $pdo): array
    {
        static $cache = null;
        if ($cache) { return $cache; }

        $candidates = ['invoice_lines','invoice_items','sales_invoice_items','invoices_items'];
        $in = implode(',', array_fill(0, count($candidates), '?'));
        $sql = "SELECT table_name FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name IN ($in)
                 LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->execute($candidates);
        $tbl = (string)$st->fetchColumn();

        if ($tbl === '') {
            // No known items table found — let caller decide or create via migration
            throw new \RuntimeException(
                "No invoice items table found. Expected one of: ".implode(', ', $candidates)
            );
        }

        $hasWh        = self::tableHasColumn($pdo, $tbl, 'warehouse_id');
        $hasLineTotal = self::tableHasColumn($pdo, $tbl, 'line_total');

        $cache = [$tbl, $hasWh, $hasLineTotal];
        return $cache;
    }

    private static function tableHasColumn(PDO $pdo, string $table, string $col): bool
    {
        $st = $pdo->prepare(
            "SELECT 1 FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?"
        );
        $st->execute([$table, $col]);
        return (bool)$st->fetchColumn();
    }
}
