<?php declare(strict_types=1);
namespace App\Models;

use App\Core\DB;
use PDO;
use App\Services\DocNumbers;

final class SalesOrder
{
  public static function nextNumber(): string {
    return DocNumbers::next('so');
  }

  public static function peekNumber(): string {
    return DocNumbers::peek('so');
  }

  public static function all(): array {
    $sql = "SELECT so.*, c.name AS customer_name
            FROM sales_orders so
            LEFT JOIN customers c ON c.id=so.customer_id
            ORDER BY so.id DESC";
    return DB::conn()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  public static function find(int $id): ?array {
    $st = DB::conn()->prepare("SELECT * FROM sales_orders WHERE id=?");
    $st->execute([$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }

  public static function items(int $id): array {
    $sql = "SELECT i.*, p.code AS product_code, p.name AS product_name, w.name AS warehouse_name
            FROM sales_order_items i
            JOIN products p ON p.id=i.product_id
            JOIN warehouses w ON w.id=i.warehouse_id
            WHERE i.sales_order_id=?";
    $st = DB::conn()->prepare($sql); $st->execute([$id]);
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
}
