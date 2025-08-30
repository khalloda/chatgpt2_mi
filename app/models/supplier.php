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
}
