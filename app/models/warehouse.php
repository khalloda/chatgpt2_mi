<?php declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class Warehouse
{
    public static function all(): array {
        $st = DB::conn()->query('SELECT id, code, name, location FROM warehouses ORDER BY name');
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    public static function find(int $id): ?array {
        $st = DB::conn()->prepare('SELECT * FROM warehouses WHERE id = ? LIMIT 1');
        $st->execute([$id]); $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }
    public static function create(string $code, string $name, ?string $loc): int {
        $st = DB::conn()->prepare('INSERT INTO warehouses (code, name, location) VALUES (?, ?, ?)');
        $st->execute([$code, $name, $loc]);
        return (int)DB::conn()->lastInsertId();
    }
    public static function update(int $id, string $code, string $name, ?string $loc): void {
        $st = DB::conn()->prepare('UPDATE warehouses SET code=?, name=?, location=? WHERE id=?');
        $st->execute([$code, $name, $loc, $id]);
    }
    public static function delete(int $id): bool {
        // block delete if any stock rows exist
        $c = DB::conn()->prepare('SELECT COUNT(*) FROM product_stocks WHERE warehouse_id=?');
        $c->execute([$id]);
        if ((int)$c->fetchColumn() > 0) return false;
        $st = DB::conn()->prepare('DELETE FROM warehouses WHERE id=?');
        return $st->execute([$id]);
    }
    public static function options(): array {
        $st = DB::conn()->query('SELECT id, name FROM warehouses ORDER BY name');
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
