<?php declare(strict_types=1);
namespace App\Models;

use App\Core\DB;
use PDO;

final class SalesReturn
{
    public static function nextNumber(): string {
        $y = date('Y');
        $st = DB::conn()->prepare("
            SELECT LPAD(COALESCE(MAX(CAST(SUBSTRING(sr_no,6) AS UNSIGNED)),0)+1,4,'0')
            FROM sales_returns
            WHERE sr_no LIKE CONCAT('SR', ?, '-%')
        ");
        $st->execute([$y]);
        $seq = (string)($st->fetchColumn() ?: '0001');
        return 'SR'.$y.'-'.$seq;
    }

    /** Items that were invoiced (per invoice id) */
    public static function invoiceItems(int $invoiceId): array {
        $sql = "SELECT sii.product_id, sii.warehouse_id, sii.qty, sii.price, sii.line_total,
                       p.code AS product_code, p.name AS product_name, w.name AS warehouse_name
                FROM sales_invoice_items sii
                JOIN products p   ON p.id = sii.product_id
                JOIN warehouses w ON w.id = sii.warehouse_id
                WHERE sii.sales_invoice_id = ?";
        $st = DB::conn()->prepare($sql); $st->execute([$invoiceId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Map: 'product_id:warehouse_id' => total returned qty (for this invoice) */
    public static function returnedMapByInvoice(int $invoiceId): array {
        $sql = "SELECT sri.product_id, sri.warehouse_id, SUM(sri.qty) AS qty
                FROM sales_return_items sri
                JOIN sales_returns sr ON sr.id = sri.sales_return_id
                WHERE sr.sales_invoice_id = ?
                GROUP BY sri.product_id, sri.warehouse_id";
        $st = DB::conn()->prepare($sql); $st->execute([$invoiceId]);
        $map = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $r) {
            $map[$r['product_id'].':'.$r['warehouse_id']] = (int)$r['qty'];
        }
        return $map;
    }

    /** Returns history lines for an invoice (expanded with product/warehouse names) */
    public static function returnsForInvoice(int $invoiceId): array {
        $sql = "SELECT sr.id AS sales_return_id, sr.sr_no, sr.created_at,
                       sri.id AS item_id, sri.product_id, sri.warehouse_id, sri.qty, sri.price, sri.line_total,
                       p.code AS product_code, p.name AS product_name, w.name AS warehouse_name
                FROM sales_returns sr
                JOIN sales_return_items sri ON sri.sales_return_id = sr.id
                JOIN products p   ON p.id = sri.product_id
                JOIN warehouses w ON w.id = sri.warehouse_id
                WHERE sr.sales_invoice_id = ?
                ORDER BY sr.id DESC, sri.id DESC";
        $st = DB::conn()->prepare($sql); $st->execute([$invoiceId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Total credits amount for an invoice (to reduce AR balance) */
    public static function creditsTotalForInvoice(int $invoiceId): float {
        $st = DB::conn()->prepare("SELECT COALESCE(SUM(total),0) FROM sales_returns WHERE sales_invoice_id=?");
        $st->execute([$invoiceId]);
        return (float)$st->fetchColumn();
    }

    public static function findHead(int $id): ?array {
        $st = DB::conn()->prepare("SELECT * FROM sales_returns WHERE id=?");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function items(int $returnId): array {
        $sql = "SELECT sri.*, p.code AS product_code, p.name AS product_name, w.name AS warehouse_name
                FROM sales_return_items sri
                JOIN products p   ON p.id = sri.product_id
                JOIN warehouses w ON w.id = sri.warehouse_id
                WHERE sri.sales_return_id = ?
                ORDER BY sri.id ASC";
        $st = DB::conn()->prepare($sql); $st->execute([$returnId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
