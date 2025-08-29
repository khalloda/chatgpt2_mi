<?php declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class Category
{
    public static function all(): array
    {
        $stmt = DB::conn()->query('SELECT c.id, c.name, c.slug, c.parent_id,
                  p.name AS parent_name
               FROM categories c
               LEFT JOIN categories p ON p.id = c.parent_id
               ORDER BY COALESCE(p.name, c.name), c.name');
        return $stmt->fetchAll() ?: [];
    }

    public static function find(int $id): ?array
    {
        $stmt = DB::conn()->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(?int $parentId, string $name, string $slug): int
    {
        $stmt = DB::conn()->prepare('INSERT INTO categories (parent_id, name, slug) VALUES (?, ?, ?)');
        $stmt->execute([$parentId, $name, $slug]);
        return (int) DB::conn()->lastInsertId();
    }

    public static function update(int $id, ?int $parentId, string $name, string $slug): void
    {
        $stmt = DB::conn()->prepare('UPDATE categories SET parent_id = ?, name = ?, slug = ? WHERE id = ?');
        $stmt->execute([$parentId, $name, $slug, $id]);
    }

    public static function delete(int $id): bool
    {
        // prevent deleting if it has children
        $check = DB::conn()->prepare('SELECT COUNT(*) FROM categories WHERE parent_id = ?');
        $check->execute([$id]);
        if ((int)$check->fetchColumn() > 0) return false;

        $stmt = DB::conn()->prepare('DELETE FROM categories WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function options(int $excludeId = 0): array
    {
        $stmt = DB::conn()->prepare('SELECT id, name FROM categories WHERE id <> ? ORDER BY name');
        $stmt->execute([$excludeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
