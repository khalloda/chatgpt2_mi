<?php declare(strict_types=1);
namespace App\Models;

use App\Core\DB;
use PDO;

final class PurchaseReturn
{
    public static function nextNumber(): string {
        $y = date('Y');
        $st = DB::conn()->prepare("
            SELECT LPAD(COALESCE(MAX(CAST(SUBSTRING(pr_no,6) AS UNSIGNED)),0)+1,4,'0')
            FROM purchase_returns
            WHERE pr_no LIKE CONCAT('PR', ?, '-%')
        ");
        $st->execute([$y]);
        $seq = (string)($st->fetchColumn() ?: '0001');
        return 'PR'.$y.'-'.$seq;
    }

    /** Map of received qty by (product,warehouse) for a PI */
    public static function receivedMapByInvoice(int $piId): array {
        $sql = "SELECT product_id, warehouse_id, SUM(qty) AS qty
                FROM receipts WHERE purchase_invoice_id=? GROUP BY product_id, warehouse_id";
        $st = DB::conn()->prepare($sql); $st->execute([$piId]);
        $map = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $r) {
            $map[$r['product_id'].':'.$r['warehouse_id']] = (int)$r['qty'];
        }
        return $map;
    }

    /** Map of already returned qty for a PI */
    public static function returnedMapByInvoice(int $piId): array {
        $sql = "SELECT pri.product_id, pri.warehouse_id, SUM(pri.qty) AS qty
                FROM purchase_return_items pri
                JOIN purchase_returns pr ON pr.id = pri.purchase_return_id
                WHERE pr.purchase_invoice_id = ?
                GROUP BY pri.product_id, pri.warehouse_id";
        $st = DB::conn()->prepare($sql); $st->execute([$piId]);
        $map = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $r) {
            $map[$r['product_id'].':'.$r['warehouse_id']] = (int)$r['qty'];
        }
        return $map;
    }

    /** History lines for a PI */
    public static function returnsForInvoice(int $piId): array {
        $sql = "SELECT pr.id AS purchase_return_id, pr.pr_no, pr.created_at,
                       pri.id AS item_id, pri.product_id, pri.warehouse_id, pri.qty, pri.price, pri.line_total,
                       p.code AS product_code, p.name AS product_name, w.name AS warehouse_name
                FROM purchase_returns pr
                JOIN purchase_return_items pri ON pri.purchase_return_id = pr.id
                JOIN products p   ON p.id = pri.product_id
                JOIN warehouses w ON w.id = pri.warehouse_id
                WHERE pr.purchase_invoice_id = ?
                ORDER BY pr.id DESC, pri.id DESC";
        $st = DB::conn()->prepare($sql); $st->execute([$piId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Total debit (credit from supplier) amount to reduce AP */
    public static function creditsTotalForInvoice(int $piId): float {
        $st = DB::conn()->prepare("SELECT COALESCE(SUM(total),0) FROM purchase_returns WHERE purchase_invoice_id=?");
        $st->execute([$piId]);
        return (float)$st->fetchColumn();
    }

    public static function findHead(int $id): ?array {
        $st = DB::conn()->prepare("SELECT * FROM purchase_returns WHERE id=?");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function items(int $id): array {
        $sql = "SELECT pri.*, p.code AS product_code, p.name AS product_name, w.name AS warehouse_name
                FROM purchase_return_items pri
                JOIN products p   ON p.id = pri.product_id
                JOIN warehouses w ON w.id = pri.warehouse_id
                WHERE pri.purchase_return_id=?
                ORDER BY pri.id ASC";
        $st = DB::conn()->prepare($sql); $st->execute([$id]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
