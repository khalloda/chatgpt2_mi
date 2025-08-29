<?php declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class Quote
{
    public static function nextNumber(): string
    {
        $y = date('Y');
        $st = DB::conn()->prepare(
            "SELECT LPAD(COALESCE(MAX(CAST(SUBSTRING(quote_no, 6) AS UNSIGNED)),0)+1,4,'0')
             FROM quotes WHERE quote_no LIKE CONCAT('Q',$y,'-%')"
        );
        $st->execute();
        $seq = (string)$st->fetchColumn();
        if ($seq === '') $seq = '0001';
        return 'Q' . $y . '-' . $seq;
    }

    public static function all(): array
    {
        $sql = "SELECT q.*, c.name AS customer_name
                FROM quotes q
                JOIN customers c ON c.id=q.customer_id
                ORDER BY q.id DESC";
        return DB::conn()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function find(int $id): ?array
    {
        $st = DB::conn()->prepare('SELECT * FROM quotes WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
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
