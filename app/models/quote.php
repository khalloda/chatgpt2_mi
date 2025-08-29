<?php declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class Customer
{
    public static function all(): array {
        $st = DB::conn()->query('SELECT id, name, phone, email FROM customers ORDER BY name');
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    public static function find(int $id): ?array {
        $st = DB::conn()->prepare('SELECT * FROM customers WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }
    public static function create(array $d): int {
        $st = DB::conn()->prepare('INSERT INTO customers (name, phone, email, address) VALUES (?,?,?,?)');
        $st->execute([$d['name'], $d['phone'], $d['email'], $d['address']]);
        return (int)DB::conn()->lastInsertId();
    }
    public static function update(int $id, array $d): void {
        $st = DB::conn()->prepare('UPDATE customers SET name=?, phone=?, email=?, address=? WHERE id=?');
        $st->execute([$d['name'], $d['phone'], $d['email'], $d['address'], $id]);
    }
    public static function delete(int $id): bool {
        // allow delete if no quotes
        $q = DB::conn()->prepare('SELECT COUNT(*) FROM quotes WHERE customer_id=?');
        $q->execute([$id]);
        if ((int)$q->fetchColumn() > 0) return false;
        $st = DB::conn()->prepare('DELETE FROM customers WHERE id=?');
        return $st->execute([$id]);
    }
    public static function options(): array {
        $st = DB::conn()->query('SELECT id, name FROM customers ORDER BY name');
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
