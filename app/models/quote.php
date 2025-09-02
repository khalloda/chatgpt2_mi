<?php declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;
use App\Services\DocNumbers;

final class Quote
{
    /** Allocate the next Quote number using centralized sequences. */
    public static function nextNumber(): string
    {
        return DocNumbers::next('q');
    }

    /** Peek without incrementing (for UI hints). */
    public static function peekNumber(): string
    {
        return DocNumbers::peek('q');
    }

    public static function all(): array
    {
        $sql = "SELECT q.*, c.name AS customer_name
                FROM quotes q
                LEFT JOIN customers c ON c.id=q.customer_id
                ORDER BY q.id DESC";
        return DB::conn()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function find(int $id): ?array
    {
        $st = DB::conn()->prepare("SELECT * FROM quotes WHERE id=?");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function items(int $quoteId): array
    {
        $sql = "SELECT qi.*, p.code AS product_code, p.name AS product_name, w.name AS warehouse_name
                FROM quote_items qi
                JOIN products p ON p.id=qi.product_id
                JOIN warehouses w ON w.id=qi.warehouse_id
                WHERE qi.quote_id=? ORDER BY qi.id";
        $st = DB::conn()->prepare($sql);
        $st->execute([$quoteId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
