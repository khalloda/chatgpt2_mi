<?php declare(strict_types=1);
namespace App\Models;

use App\Core\DB;
use PDO;

final class Supplier
{
    public static function all(): array {
        $sql = "SELECT * FROM suppliers ORDER BY name";
        return DB::conn()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function find(int $id): ?array {
        $st = DB::conn()->prepare("SELECT * FROM suppliers WHERE id=?");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(array $d): int {
        $st = DB::conn()->prepare("INSERT INTO suppliers (name, phone, email, address) VALUES (?,?,?,?)");
        $st->execute([$d['name'], $d['phone'], $d['email'], $d['address']]);
        return (int)DB::conn()->lastInsertId();
    }

    public static function update(int $id, array $d): void {
        $st = DB::conn()->prepare("UPDATE suppliers SET name=?, phone=?, email=?, address=? WHERE id=?");
        $st->execute([$d['name'], $d['phone'], $d['email'], $d['address'], $id]);
    }

    public static function delete(int $id): void {
        DB::conn()->prepare("DELETE FROM suppliers WHERE id=?")->execute([$id]);
    }
	public static function allWithBalance(): array {
    $sql = "SELECT s.*,
                   COALESCE(pi_sum,0) - COALESCE(pay_sum,0) AS balance
            FROM suppliers s
            LEFT JOIN (
              SELECT supplier_id, SUM(total) AS pi_sum
              FROM purchase_invoices GROUP BY supplier_id
            ) t1 ON t1.supplier_id = s.id
            LEFT JOIN (
              SELECT supplier_id, SUM(amount) AS pay_sum
              FROM supplier_payments GROUP BY supplier_id
            ) t2 ON t2.supplier_id = s.id
            ORDER BY s.name";
    return DB::conn()->query($sql)->fetchAll(\PDO::FETCH_ASSOC) ?: [];
}
/** Opening balance strictly before $from (invoices - payments - returns) */
    public static function apOpeningBalance(int $supplierId, string $from): float {
        $pdo = DB::conn();

        $sqlInv = "SELECT COALESCE(SUM(total),0) FROM purchase_invoices
                   WHERE supplier_id=? AND created_at < ?";
        $sqlPay = "SELECT COALESCE(SUM(amount),0) FROM supplier_payments
                   WHERE supplier_id=? AND paid_at < ?";
        $sqlRet = "SELECT COALESCE(SUM(total),0) FROM purchase_returns
                   WHERE supplier_id=? AND created_at < ?";

        $inv = (float)self::scalar($pdo, $sqlInv, [$supplierId, $from]);
        $pay = (float)self::scalar($pdo, $sqlPay, [$supplierId, $from]);
        $ret = (float)self::scalar($pdo, $sqlRet, [$supplierId, $from]);

        return $inv - $pay - $ret;
    }

    /** Movements between dates inclusive. Debit increases AP, Credit decreases. */
    public static function apMovements(int $supplierId, string $from, string $to): array {
        $pdo = DB::conn();

        // Invoices (debit)
        $q1 = $pdo->prepare("
            SELECT created_at AS txn_date, 'invoice' AS kind, pi_no AS ref_no,
                   total AS debit, 0 AS credit, id AS ref_id
            FROM purchase_invoices
            WHERE supplier_id=? AND DATE(created_at) BETWEEN ? AND ?
        ");
        $q1->execute([$supplierId, $from, $to]);
        $invoices = $q1->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Payments (credit)
        $q2 = $pdo->prepare("
            SELECT paid_at AS txn_date, 'payment' AS kind, reference AS ref_no,
                   0 AS debit, amount AS credit, id AS ref_id
            FROM supplier_payments
            WHERE supplier_id=? AND DATE(paid_at) BETWEEN ? AND ?
        ");
        $q2->execute([$supplierId, $from, $to]);
        $payments = $q2->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Purchase returns / debit notes (credit)
        $q3 = $pdo->prepare("
            SELECT created_at AS txn_date, 'return' AS kind, pr_no AS ref_no,
                   0 AS debit, total AS credit, id AS ref_id
            FROM purchase_returns
            WHERE supplier_id=? AND DATE(created_at) BETWEEN ? AND ?
        ");
        $q3->execute([$supplierId, $from, $to]);
        $returns = $q3->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $all = array_merge($invoices, $payments, $returns);

        usort($all, function($a,$b){
            if ($a['txn_date'] === $b['txn_date']) return $a['kind'] <=> $b['kind'];
            return strcmp($a['txn_date'], $b['txn_date']);
        });

        return $all;
    }

    /** AP aging snapshot grouped by supplier as of a date (inclusive) */
    public static function apAgingSnapshot(string $asOf): array {
        $pdo = DB::conn();

        // Outstanding per invoice = total - payments_to_invoice - returns_for_invoice
        // Then bucket by invoice age (days since created_at)
        $sql = "
        SELECT s.id AS supplier_id, s.name AS supplier_name,
               SUM(CASE WHEN age<=30  THEN outstanding ELSE 0 END) AS bucket_0_30,
               SUM(CASE WHEN age BETWEEN 31 AND 60 THEN outstanding ELSE 0 END) AS bucket_31_60,
               SUM(CASE WHEN age BETWEEN 61 AND 90 THEN outstanding ELSE 0 END) AS bucket_61_90,
               SUM(CASE WHEN age>90  THEN outstanding ELSE 0 END) AS bucket_90_plus,
               SUM(outstanding) AS total
        FROM (
          SELECT pi.id, pi.supplier_id,
                 GREATEST(
                   pi.total
                   - COALESCE((
                       SELECT SUM(sp.amount) FROM supplier_payments sp
                       WHERE sp.purchase_invoice_id=pi.id AND sp.paid_at<=?
                     ),0)
                   - COALESCE((
                       SELECT SUM(pr.total) FROM purchase_returns pr
                       WHERE pr.purchase_invoice_id=pi.id AND pr.created_at<=?
                     ),0)
                 ,0) AS outstanding,
                 DATEDIFF(?, pi.created_at) AS age
          FROM purchase_invoices pi
          WHERE pi.created_at<=?
        ) x
        JOIN suppliers s ON s.id = x.supplier_id
        WHERE outstanding > 0
        GROUP BY s.id, s.name
        ORDER BY s.name";

        $st = $pdo->prepare($sql);
        $st->execute([$asOf, $asOf, $asOf, $asOf]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private static function scalar(\PDO $pdo, string $sql, array $params) {
        $st = $pdo->prepare($sql); $st->execute($params);
        return $st->fetchColumn();
    }
}

