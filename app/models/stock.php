<?php
namespace App\Models;

use App\Core\DB;
use PDO;

final class Stock
{
    /**
     * Post a goods receipt line.
     *
     * @param int    $product_id
     * @param int    $warehouse_id
     * @param float  $qty_received          // positive
     * @param float  $unit_cost             // cost per unit from PI
     * @return void
     */
    public static function receive(int $product_id, int $warehouse_id, float $qty_received, float $unit_cost): void
    {
        if ($qty_received <= 0) return;

        $pdo = DB::conn();
        $pdo->beginTransaction();
        try {
            // Create row if missing (unique key on product_id+warehouse_id is assumed)
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS product_stocks (
                  product_id INT NOT NULL,
                  warehouse_id INT NOT NULL,
                  qty_on_hand DECIMAL(18,4) NOT NULL DEFAULT 0,
                  qty_reserved DECIMAL(18,4) NOT NULL DEFAULT 0,
                  avg_cost DECIMAL(18,6) NOT NULL DEFAULT 0,
                  PRIMARY KEY (product_id, warehouse_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            // Lock current stock
            $sel = $pdo->prepare("SELECT qty_on_hand, avg_cost FROM product_stocks WHERE product_id = ? AND warehouse_id = ? FOR UPDATE");
            $sel->execute([$product_id, $warehouse_id]);
            $row = $sel->fetch(PDO::FETCH_ASSOC);

            $old_qty  = $row ? (float)$row['qty_on_hand'] : 0.0;
            $old_avg  = $row ? (float)$row['avg_cost']    : 0.0;
            $recv_qty = $qty_received;
            $recv_cost= $unit_cost;

            $new_qty  = $old_qty + $recv_qty;
            $new_avg  = $new_qty > 0
                ? (($old_qty * $old_avg) + ($recv_qty * $recv_cost)) / $new_qty
                : $recv_cost;

            if ($row) {
                $upd = $pdo->prepare("UPDATE product_stocks
                                      SET qty_on_hand = ?, avg_cost = ?
                                      WHERE product_id = ? AND warehouse_id = ?");
                $upd->execute([$new_qty, $new_avg, $product_id, $warehouse_id]);
            } else {
                $ins = $pdo->prepare("INSERT INTO product_stocks (product_id, warehouse_id, qty_on_hand, avg_cost)
                                      VALUES (?, ?, ?, ?)");
                $ins->execute([$product_id, $warehouse_id, $recv_qty, $recv_cost]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
