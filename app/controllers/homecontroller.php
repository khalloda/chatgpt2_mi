<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;               // <- this project uses DB::conn()
use PDO;

final class HomeController extends Controller
{
    public function index(): void
    {
        $pdo = DB::conn();

        // Connectivity probe
        $db_ok = true;
        $db_error = '';
        try {
            $pdo->query('SELECT 1');
        } catch (\Throwable $e) {
            $db_ok = false;
            $db_error = $e->getMessage();
        }

        // Helper: scalar query with params; returns 0 on failure
        $scalar = function (string $sql, array $params = []) use ($pdo): float {
            try {
                $st = $pdo->prepare($sql);
                $st->execute($params);
                $v = $st->fetchColumn();
                return $v !== false ? (float)$v : 0.0;
            } catch (\Throwable $e) {
                return 0.0;
            }
        };

        $today     = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('-6 days'));

        // If you don't have product-specific reorder levels yet, use a small threshold
        $lowThreshold = 5;

        // KPIs (aligned to your schema)
        $kpi = [
            // Inventory value = sum(on_hand * avg_cost) across all warehouses
            'stock_value' => $scalar("SELECT COALESCE(SUM(ps.qty_on_hand * ps.avg_cost),0)
                                      FROM product_stocks ps"),

            // Out of stock (available <= 0) per product across warehouses
            'out_of_stock' => $scalar("
                SELECT COUNT(*) FROM (
                    SELECT p.id,
                           COALESCE(SUM(ps.qty_on_hand - ps.qty_reserved), 0) AS avail
                    FROM products p
                    LEFT JOIN product_stocks ps ON ps.product_id = p.id
                    GROUP BY p.id
                    HAVING avail <= 0
                ) t
            "),

            // Low stock (available <= threshold) – until per-product reorder levels exist
            'low_stock' => $scalar("
                SELECT COUNT(*) FROM (
                    SELECT p.id,
                           COALESCE(SUM(ps.qty_on_hand - ps.qty_reserved), 0) AS avail
                    FROM products p
                    LEFT JOIN product_stocks ps ON ps.product_id = p.id
                    GROUP BY p.id
                    HAVING avail > 0 AND avail <= ?
                ) t
            ", [$lowThreshold]),

            // This week's activity
            'quotes_week'   => $scalar("SELECT COUNT(*) FROM quotes
                                        WHERE DATE(created_at) BETWEEN ? AND ?", [$weekStart, $today]),
            'orders_week'   => $scalar("SELECT COUNT(*) FROM sales_orders
                                        WHERE DATE(created_at) BETWEEN ? AND ?", [$weekStart, $today]),
            'invoices_week' => $scalar("SELECT COUNT(*) FROM invoices
                                        WHERE DATE(created_at) BETWEEN ? AND ?", [$weekStart, $today]),

            // Open AR (no due_date column yet → treat unpaid/partial as 'open')
            'overdue_ar' => $scalar("SELECT COUNT(*) FROM invoices
                                     WHERE status IN ('unpaid','partial')
                                        OR (total - paid_amount) > 0"),
        ];

        // Best-seller this week (by qty)
        try {
            $stmt = $pdo->prepare("
                SELECT p.name, SUM(ii.qty) AS qty
                FROM invoice_items ii
                JOIN invoices i ON i.id = ii.invoice_id
                JOIN products p ON p.id = ii.product_id
                WHERE DATE(i.created_at) BETWEEN ? AND ?
                GROUP BY p.id
                ORDER BY qty DESC
                LIMIT 1
            ");
            $stmt->execute([$weekStart, $today]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            $kpi['best_seller'] = $row ? ['name' => (string)$row['name'], 'qty' => (int)$row['qty']]
                                       : ['name' => '—', 'qty' => 0];
        } catch (\Throwable $e) {
            $kpi['best_seller'] = ['name' => '—', 'qty' => 0];
        }

        // Small "Recent" lists for the dashboard tables
        $quotes = $pdo->query("SELECT id, quote_no, status, total, created_at
                               FROM quotes ORDER BY created_at DESC LIMIT 5")
                      ->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $orders = $pdo->query("SELECT id, so_no, status, total, created_at
                               FROM sales_orders ORDER BY created_at DESC LIMIT 5")
                      ->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $invoices = $pdo->query("SELECT id, inv_no, status, total, paid_amount, created_at
                                 FROM invoices ORDER BY created_at DESC LIMIT 5")
                        ->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->view('home/index', compact('kpi', 'db_ok', 'db_error', 'quotes', 'orders', 'invoices'));
    }
}
