<?php declare(strict_types=1);
namespace App\Models;

use App\Core\DB;
use PDO;

final class SalesReturn
{
    /** SRYYYY-#### (per year) */
    public static function nextNumber(): string {
        $y = date('Y');
        $st = DB::conn()->prepare("
            SELECT LPAD(COALESCE(MAX(CAST(SUBSTRING(sr_no,6) AS UNSIGNED)),0)+1,4,'0')
            FROM sales_returns
            WHERE sr_no LIKE CONCAT('SR', ?, '-%')
        ");
        $st->execute([$y]);
        $seq = (string)$st->fetchColumn();
        if ($seq === '' || $seq === null) { $seq = '0001'; }
        return 'SR'.$y.'-'.$seq;
    }

    /** Returned quantities map for an invoice: key "product:warehouse" => qty */
    public static function returnedMapByInvoice(int $invoiceId): array {
        $sql = "SELECT sri.product_id, sri.warehouse_id, SUM(sri.qty) AS qty
                  FROM sales_return_items sri
                  JOIN sales_returns sr ON sr.id = sri.sales_return_id
                 WHERE sr.sales_invoice_id = ?
                 GROUP BY sri.product_id, sri.warehouse_id";
        $st = DB::conn()->prepare($sql);
        $st->execute([$invoiceId]);
        $map = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $r) {
            $map[(int)$r['product_id'].':'.(int)$r['warehouse_id']] = (int)$r['qty'];
        }
        return $map;
    }

    /** History rows for an invoice (used under invoice view) */
    public static function returnsForInvoice(int $invoiceId): array {
        $sql = "SELECT 
                    sri.sales_return_id,
                    sr.sr_no,
                    sr.created_at AS created_at,
                    p.code AS product_code,
                    p.name AS product_name,
                    w.name AS warehouse_name,
                    sri.qty,
                    sri.price
                FROM sales_return_items sri
                JOIN sales_returns sr ON sr.id = sri.sales_return_id
                JOIN products p       ON p.id = sri.product_id
                JOIN warehouses w     ON w.id = sri.warehouse_id
                WHERE sr.sales_invoice_id = ?
                ORDER BY sr.id DESC, sri.id ASC";
        $st = DB::conn()->prepare($sql);
        $st->execute([$invoiceId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Items for a specific credit note */
    public static function items(int $returnId): array {
        $sql = "SELECT 
                    sri.*,
                    p.code AS product_code,
                    p.name AS product_name,
                    w.name AS warehouse_name
                FROM sales_return_items sri
                JOIN products p   ON p.id = sri.product_id
                JOIN warehouses w ON w.id = sri.warehouse_id
                WHERE sri.sales_return_id = ?
                ORDER BY sri.id ASC";
        $st = DB::conn()->prepare($sql);
        $st->execute([$returnId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Sum of credits for an invoice (used for balance in invoice view) */
    public static function creditsTotalForInvoice(int $invoiceId): float {
        $st = DB::conn()->prepare("SELECT COALESCE(SUM(total),0) FROM sales_returns WHERE sales_invoice_id=?");
        $st->execute([$invoiceId]);
        return (float)$st->fetchColumn();
    }
}
