<?php declare(strict_types=1);
namespace App\Models;

use App\Core\DB;
use PDO;

final class Invoice
{
    public static function nextNumber(): string {
        $y = date('Y');
        $st = DB::conn()->prepare("SELECT LPAD(COALESCE(MAX(CAST(SUBSTRING(inv_no,6) AS UNSIGNED)),0)+1,4,'0')
                                   FROM invoices WHERE inv_no LIKE CONCAT('INV',$y,'-%')");
        $st->execute();
        $seq = (string)($st->fetchColumn() ?: '0001');
        return 'INV'.$y.'-'.$seq;
    }

    public static function all(): array {
        $sql = "SELECT i.*, c.name AS customer_name
                FROM invoices i JOIN customers c ON c.id=i.customer_id
                ORDER BY i.id DESC";
        return DB::conn()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function find(int $id): ?array {
        $st = DB::conn()->prepare("SELECT i.*, c.name AS customer_name
                                   FROM invoices i JOIN customers c ON c.id=i.customer_id
                                   WHERE i.id=?");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function items(int $id): array {
        $sql = "SELECT it.*, p.code AS product_code, p.name AS product_name, w.name AS warehouse_name
                FROM invoice_items it
                JOIN products p ON p.id=it.product_id
                JOIN warehouses w ON w.id=it.warehouse_id
                WHERE it.invoice_id=?";
        $st = DB::conn()->prepare($sql); $st->execute([$id]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function payments(int $id): array {
        $st = DB::conn()->prepare("SELECT * FROM invoice_payments WHERE invoice_id=? ORDER BY id DESC");
        $st->execute([$id]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function recalcPaidAmount(int $id): void {
        $pdo = DB::conn();
        $sum = (float)($pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM invoice_payments WHERE invoice_id=?")
                            ->execute([$id]) ? $pdo->query("SELECT COALESCE(SUM(amount),0) FROM invoice_payments WHERE invoice_id={$id}")->fetchColumn() : 0);
        // safer:
        $st = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM invoice_payments WHERE invoice_id=?");
        $st->execute([$id]); $sum = (float)$st->fetchColumn();

        $st2 = $pdo->prepare("SELECT total FROM invoices WHERE id=?");
        $st2->execute([$id]); $total = (float)$st2->fetchColumn();

        $status = ($sum <= 0.0) ? 'unpaid' : (($sum + 0.00001 < $total) ? 'partial' : 'paid');
        $upd = $pdo->prepare("UPDATE invoices SET paid_amount=?, status=? WHERE id=?");
        $upd->execute([$sum, $status, $id]);
    }
}
