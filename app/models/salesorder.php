<?php declare(strict_types=1);
namespace App\Models;
use App\Core\DB; use PDO;

final class SalesOrder
{
  public static function nextNumber(): string {
    $y = date('Y');
    $st = DB::conn()->prepare("SELECT LPAD(COALESCE(MAX(CAST(SUBSTRING(so_no,6) AS UNSIGNED)),0)+1,4,'0')
                               FROM sales_orders WHERE so_no LIKE CONCAT('SO',$y,'-%')");
    $st->execute();
    $seq = (string)($st->fetchColumn() ?: '0001');
    return 'SO'.$y.'-'.$seq;
  }

  public static function all(): array {
    $sql = "SELECT so.*, c.name AS customer_name
            FROM sales_orders so JOIN customers c ON c.id=so.customer_id
            ORDER BY so.id DESC";
    return DB::conn()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
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
