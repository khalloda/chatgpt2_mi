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
}
