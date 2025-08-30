<?php declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class Note
{
    public static function for(string $entityType, int $entityId): array
    {
        $st = DB::conn()->prepare(
            'SELECT id, is_public, body, created_by, created_at
             FROM notes
             WHERE entity_type = ? AND entity_id = ?
             ORDER BY id DESC'
        );
        $st->execute([$entityType, $entityId]);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public static function publicFor(string $entityType, int $entityId): array
    {
        $st = DB::conn()->prepare(
            'SELECT id, body, created_by, created_at
             FROM notes
             WHERE entity_type = ? AND entity_id = ? AND is_public = 1
             ORDER BY id DESC'
        );
        $st->execute([$entityType, $entityId]);
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public static function create(array $d): int
    {
        $st = DB::conn()->prepare(
            'INSERT INTO notes (entity_type, entity_id, is_public, body, created_by, created_by_id)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $d['entity_type'], $d['entity_id'], $d['is_public'] ? 1 : 0,
            $d['body'], $d['created_by'], $d['created_by_id'] ?? null
        ]);
        return (int)DB::conn()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        DB::conn()->prepare('DELETE FROM notes WHERE id = ?')->execute([$id]);
    }
	
	public static function update(int $id, string $body, bool $isPublic): void
{
    DB::conn()->prepare(
        'UPDATE notes SET body = ?, is_public = ? WHERE id = ?'
    )->execute([$body, $isPublic ? 1 : 0, $id]);
}
	
}
