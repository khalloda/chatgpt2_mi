<?php declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class Make
{
    public static function all(): array
    {
        $stmt = DB::conn()->query('SELECT id, name, slug FROM makes ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function find(int $id): ?array
    {
        $stmt = DB::conn()->prepare('SELECT * FROM makes WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(string $name, string $slug): int
    {
        $stmt = DB::conn()->prepare('INSERT INTO makes (name, slug) VALUES (?, ?)');
        $stmt->execute([$name, $slug]);
        return (int) DB::conn()->lastInsertId();
    }

    public static function update(int $id, string $name, string $slug): void
    {
        $stmt = DB::conn()->prepare('UPDATE makes SET name = ?, slug = ? WHERE id = ?');
        $stmt->execute([$name, $slug, $id]);
    }

    public static function delete(int $id): bool
    {
        // block delete if models exist
        $check = DB::conn()->prepare('SELECT COUNT(*) FROM vehicle_models WHERE make_id = ?');
        $check->execute([$id]);
        if ((int)$check->fetchColumn() > 0) return false;

        $stmt = DB::conn()->prepare('DELETE FROM makes WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function options(): array
    {
        $stmt = DB::conn()->query('SELECT id, name FROM makes ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
