<?php declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class Product
{
    public static function nextCode(): string
    {
        $st = DB::conn()->query("SELECT LPAD(IFNULL(MAX(CAST(SUBSTRING(code,4) AS UNSIGNED)),0)+1,4,'0') AS seq
                                 FROM products WHERE code REGEXP '^PRD[0-9]+$'");
        $seq = (string)($st->fetchColumn() ?: '0001');
        return 'PRD' . $seq;
    }

    public static function all(?string $q = null, ?int $cat = null, ?int $make = null, ?int $model = null): array
    {
        $sql = "SELECT p.*, c.name AS category_name, mk.name AS make_name, vm.name AS model_name,
                       COALESCE(SUM(ps.qty_on_hand),0) AS on_hand,
                       COALESCE(SUM(ps.qty_reserved),0) AS reserved
                FROM products p
                LEFT JOIN categories c ON c.id=p.category_id
                LEFT JOIN makes mk ON mk.id=p.make_id
                LEFT JOIN vehicle_models vm ON vm.id=p.model_id
                LEFT JOIN product_stocks ps ON ps.product_id=p.id
                WHERE 1=1";
        $args = [];
        if ($q)   { $sql .= " AND (p.name LIKE ? OR p.code LIKE ?)"; $args[]="%$q%"; $args[]="%$q%"; }
        if ($cat) { $sql .= " AND p.category_id=?"; $args[]=$cat; }
        if ($make){ $sql .= " AND p.make_id=?";     $args[]=$make; }
        if ($model){$sql.=" AND p.model_id=?";      $args[]=$model; }
        $sql .= " GROUP BY p.id ORDER BY p.name";

        $st = DB::conn()->prepare($sql);
        $st->execute($args);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function find(int $id): ?array
    {
        $st = DB::conn()->prepare('SELECT * FROM products WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function create(array $data): int
    {
        $st = DB::conn()->prepare('INSERT INTO products (code, name, category_id, make_id, model_id, cost, price)
                                   VALUES (?, ?, ?, ?, ?, ?, ?)');
        $st->execute([
            $data['code'], $data['name'],
            $data['category_id'] ?: null,
            $data['make_id'] ?: null,
            $data['model_id'] ?: null,
            $data['cost'], $data['price']
        ]);
        return (int)DB::conn()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $st = DB::conn()->prepare('UPDATE products SET code=?, name=?, category_id=?, make_id=?, model_id=?, cost=?, price=? WHERE id=?');
        $st->execute([
            $data['code'], $data['name'],
            $data['category_id'] ?: null,
            $data['make_id'] ?: null,
            $data['model_id'] ?: null,
            $data['cost'], $data['price'],
            $id
        ]);
    }

    public static function delete(int $id): bool
    {
        $st = DB::conn()->prepare('DELETE FROM products WHERE id=?');
        return $st->execute([$id]);
    }

    /** stocks **/

    public static function stocks(int $productId): array
    {
        $st = DB::conn()->prepare('SELECT w.id, w.name,
                  COALESCE(ps.qty_on_hand,0) AS qty_on_hand,
                  COALESCE(ps.qty_reserved,0) AS qty_reserved
               FROM warehouses w
               LEFT JOIN product_stocks ps ON ps.warehouse_id=w.id AND ps.product_id=?
               ORDER BY w.name');
        $st->execute([$productId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function saveStocks(int $productId, array $rows): void
    {
        $pdo = DB::conn();
        $ins = $pdo->prepare('INSERT INTO product_stocks (product_id, warehouse_id, qty_on_hand, qty_reserved)
                              VALUES (?, ?, ?, ?)
                              ON DUPLICATE KEY UPDATE qty_on_hand=VALUES(qty_on_hand), qty_reserved=VALUES(qty_reserved)');
        foreach ($rows as $wid => $pair) {
            $on  = max(0, (int)($pair['on'] ?? 0));
            $res = max(0, (int)($pair['res'] ?? 0));
            $ins->execute([$productId, (int)$wid, $on, $res]);
        }
    }
	
	public static function adjustReserved(int $productId, int $warehouseId, int $delta): void
	{
    $pdo = DB::conn();
    // upsert then clamp at >= 0
    $pdo->prepare('INSERT INTO product_stocks (product_id, warehouse_id, qty_on_hand, qty_reserved)
                   VALUES (?, ?, 0, ?)
                   ON DUPLICATE KEY UPDATE qty_reserved = GREATEST(0, qty_reserved + VALUES(qty_reserved))')
        ->execute([$productId, $warehouseId, $delta]);
	}

public static function consumeFromReservation(int $productId, int $warehouseId, int $qty): void
{
    // qty_reserved -= qty, qty_on_hand -= qty (clamped at >=0), single atomic statement
    DB::conn()->prepare("
      INSERT INTO product_stocks (product_id, warehouse_id, qty_on_hand, qty_reserved)
      VALUES (?, ?, 0, 0)
      ON DUPLICATE KEY UPDATE
        qty_reserved = GREATEST(0, qty_reserved - ?),
        qty_on_hand = GREATEST(0, qty_on_hand - ?)
    ")->execute([$productId, $warehouseId, $qty, $qty]);
}
public static function canFulfill(int $productId, int $warehouseId, int $qty): bool
{
    $st = DB::conn()->prepare(
        'SELECT qty_on_hand, qty_reserved FROM product_stocks WHERE product_id=? AND warehouse_id=?'
    );
    $st->execute([$productId, $warehouseId]);
    $row = $st->fetch(\PDO::FETCH_ASSOC);
    $on  = (int)($row['qty_on_hand'] ?? 0);
    // here we check we have enough on-hand to consume the reservation
    return $on >= $qty;
}

}
