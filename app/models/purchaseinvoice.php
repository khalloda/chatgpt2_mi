<?php declare(strict_types=1);
namespace App\Models;

use App\Core\DB;
use PDO;

final class PurchaseInvoice
{
    public static function nextNumber(): string {
        $y = date('Y');
        $st = DB::conn()->prepare("SELECT LPAD(COALESCE(MAX(CAST(SUBSTRING(pi_no,6) AS UNSIGNED)),0)+1,4,'0')
                                   FROM purchase_invoices WHERE pi_no LIKE CONCAT('PI',$y,'-%')");
        $st->execute();
        $seq = (string)($st->fetchColumn() ?: '0001');
        return 'PI'.$y.'-'.$seq;
    }

    public static function all(): array {
        $sql = "SELECT pi.*, s.name AS supplier_name, po.po_no
                FROM purchase_invoices pi
                JOIN suppliers s ON s.id = pi.supplier_id
                JOIN purchase_orders po ON po.id = pi.purchase_order_id
                ORDER BY pi.id DESC";
        return DB::conn()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function find(int $id): ?array {
        $st = DB::conn()->prepare("SELECT pi.*, s.name AS supplier_name, po.po_no
                                   FROM purchase_invoices pi
                                   JOIN suppliers s ON s.id = pi.supplier_id
                                   JOIN purchase_orders po ON po.id = pi.purchase_order_id
                                   WHERE pi.id = ?");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Items come from the PO that this PI belongs to */
    public static function poItems(int $invoiceId): array {
        $sql = "SELECT i.*, p.code AS product_code, p.name AS product_name, w.name AS warehouse_name
                FROM purchase_invoices pi
                JOIN purchase_orders po ON po.id = pi.purchase_order_id
                JOIN purchase_order_items i ON i.purchase_order_id = po.id
                JOIN products p ON p.id = i.product_id
                JOIN warehouses w ON w.id = i.warehouse_id
                WHERE pi.id = ?";
        $st = DB::conn()->prepare($sql); $st->execute([$invoiceId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Map of received qty so far for this PI's PO: key "product_id:warehouse_id" => qty */
    public static function receivedMapByPo(int $poId): array {
        $sql = "SELECT r.product_id, r.warehouse_id, SUM(r.qty) AS qty
                FROM receipts r
                JOIN purchase_invoices pi ON pi.id = r.purchase_invoice_id
                WHERE pi.purchase_order_id = ?
                GROUP BY r.product_id, r.warehouse_id";
        $st = DB::conn()->prepare($sql); $st->execute([$poId]);
        $map = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $map[$row['product_id'].':'.$row['warehouse_id']] = (int)$row['qty'];
        }
        return $map;
    }
	
	public static function receipts(int $invoiceId): array {
    $sql = "SELECT r.*, p.code AS product_code, p.name AS product_name, w.name AS warehouse_name
            FROM receipts r
            JOIN products p   ON p.id = r.product_id
            JOIN warehouses w ON w.id = r.warehouse_id
            WHERE r.purchase_invoice_id = ?
            ORDER BY r.id DESC";
    $st = DB::conn()->prepare($sql);
    $st->execute([$invoiceId]);
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
public static function recalcPaidAmount(int $invoiceId): void
{
    $pdo = DB::conn();

    $st = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM supplier_payments WHERE purchase_invoice_id=?");
    $st->execute([$invoiceId]);
    $paid = (float)$st->fetchColumn();

    $st2 = $pdo->prepare("SELECT total FROM purchase_invoices WHERE id=?");
    $st2->execute([$invoiceId]);
    $total = (float)($st2->fetchColumn() ?: 0);

    $status = 'unpaid';
    if ($paid <= 0.0) {
        $status = 'unpaid';
    } elseif ($paid + 0.0001 < $total) {
        $status = 'partial';
    } else {
        $paid = min($paid, $total); // cap display
        $status = 'paid';
    }

    $upd = $pdo->prepare("UPDATE purchase_invoices SET paid_amount=?, status=? WHERE id=?");
    $upd->execute([$paid, $status, $invoiceId]);
}
}
