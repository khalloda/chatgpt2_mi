<?php declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class VehicleModel
{
    public static function all(?int $makeId = null): array
    {
        if ($makeId) {
            $stmt = DB::conn()->prepare('SELECT vm.id, vm.name, vm.slug, m.name AS make_name, vm.make_id
                                         FROM vehicle_models vm
                                         JOIN makes m ON m.id = vm.make_id
                                         WHERE vm.make_id = ?
                                         ORDER BY m.name, vm.name');
            $stmt->execute([$makeId]);
        } else {
            $stmt = DB::conn()->query('SELECT vm.id, vm.name, vm.slug, m.name AS make_name, vm.make_id
                                       FROM vehicle_models vm
                                       JOIN makes m ON m.id = vm.make_id
                                       ORDER BY m.name, vm.name');
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function find(int $id): ?array
    {
        $stmt = DB::conn()->prepare('SELECT * FROM vehicle_models WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(int $makeId, string $name, string $slug): int
    {
        $stmt = DB::conn()->prepare('INSERT INTO vehicle_models (make_id, name, slug) VALUES (?, ?, ?)');
        $stmt->execute([$makeId, $name, $slug]);
        return (int) DB::conn()->lastInsertId();
    }

    public static function update(int $id, int $makeId, string $name, string $slug): void
    {
        $stmt = DB::conn()->prepare('UPDATE vehicle_models SET make_id = ?, name = ?, slug = ? WHERE id = ?');
        $stmt->execute([$makeId, $name, $slug, $id]);
    }

    public static function delete(int $id): bool
    {
        $stmt = DB::conn()->prepare('DELETE FROM vehicle_models WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
