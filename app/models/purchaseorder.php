<?php declare(strict_types=1);
namespace App\Models;

use App\Core\DB;
use PDO;

final class PurchaseOrder
{
    public static function nextNumber(): string {
        $y = date('Y');
        $st = DB::conn()->prepare("SELECT LPAD(COALESCE(MAX(CAST(SUBSTRING(po_no,6) AS UNSIGNED)),0)+1,4,'0')
                                   FROM purchase_orders WHERE po_no LIKE CONCAT('PO',$y,'-%')");
        $st->execute();
        $seq = (string)($st->fetchColumn() ?: '0001');
        return 'PO'.$y.'-'.$seq;
    }

    public static function all(): array {
        $sql = "SELECT po.*, s.name AS supplier_name
                FROM purchase_orders po
                JOIN suppliers s ON s.id = po.supplier_id
                ORDER BY po.id DESC";
        return DB::conn()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function find(int $id): ?array {
        $st = DB::conn()->prepare("SELECT po.*, s.name AS supplier_name
                                   FROM purchase_orders po
                                   JOIN suppliers s ON s.id = po.supplier_id
                                   WHERE po.id=?");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function items(int $poId): array {
        $sql = "SELECT i.*, p.code AS product_code, p.name AS product_name, w.name AS warehouse_name
                FROM purchase_order_items i
                JOIN products p ON p.id = i.product_id
                JOIN warehouses w ON w.id = i.warehouse_id
                WHERE i.purchase_order_id = ?";
        $st = DB::conn()->prepare($sql); $st->execute([$poId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
