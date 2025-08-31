<?php declare(strict_types=1);
namespace App\Models;

use App\Core\DB;
use PDO;

final class SupplierPayment
{
    public static function forInvoice(int $invoiceId): array {
        $sql = "SELECT sp.*, s.name AS supplier_name
                FROM supplier_payments sp
                JOIN suppliers s ON s.id = sp.supplier_id
                WHERE sp.purchase_invoice_id = ?
                ORDER BY sp.paid_at DESC, sp.id DESC";
        $st = DB::conn()->prepare($sql);
        $st->execute([$invoiceId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function all(int $limit = 200): array {
        $sql = "SELECT sp.*, s.name AS supplier_name, pi.pi_no
                FROM supplier_payments sp
                JOIN suppliers s ON s.id = sp.supplier_id
                JOIN purchase_invoices pi ON pi.id = sp.purchase_invoice_id
                ORDER BY sp.paid_at DESC, sp.id DESC
                LIMIT ?";
        $st = DB::conn()->prepare($sql);
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function create(array $d): int {
        $st = DB::conn()->prepare("INSERT INTO supplier_payments (supplier_id, purchase_invoice_id, paid_at, method, reference, amount, note)
                                   VALUES (?,?,?,?,?,?,?)");
        $st->execute([
            (int)$d['supplier_id'], (int)$d['purchase_invoice_id'], (string)$d['paid_at'],
            (string)$d['method'], (string)($d['reference'] ?? ''), (float)$d['amount'], (string)($d['note'] ?? '')
        ]);
        return (int)DB::conn()->lastInsertId();
    }

    public static function delete(int $id): void {
        DB::conn()->prepare("DELETE FROM supplier_payments WHERE id=?")->execute([$id]);
    }
}
