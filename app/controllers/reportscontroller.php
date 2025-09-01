<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;

use function App\Core\require_auth;

final class ReportsController extends Controller
{
    /** AP Aging (existing) */
    public function apaging(): void {
        require_auth();
        $asof = $_GET['asof'] ?? date('Y-m-d');

        $pdo = DB::conn();
        $sql = "
        SELECT s.id AS supplier_id, s.name AS supplier_name,
               SUM(CASE WHEN age<=30  THEN outstanding ELSE 0 END) AS bucket_0_30,
               SUM(CASE WHEN age BETWEEN 31 AND 60 THEN outstanding ELSE 0 END) AS bucket_31_60,
               SUM(CASE WHEN age BETWEEN 61 AND 90 THEN outstanding ELSE 0 END) AS bucket_61_90,
               SUM(CASE WHEN age>90  THEN outstanding ELSE 0 END) AS bucket_90_plus,
               SUM(outstanding) AS total
        FROM (
          SELECT pi.id, pi.supplier_id,
                 GREATEST(
                   pi.total
                   - COALESCE((
                       SELECT SUM(sp.amount) FROM supplier_payments sp
                       WHERE sp.purchase_invoice_id=pi.id AND sp.paid_at<=?
                     ),0)
                   - COALESCE((
                       SELECT SUM(pr.total) FROM purchase_returns pr
                       WHERE pr.purchase_invoice_id=pi.id AND pr.created_at<=?
                     ),0)
                 ,0) AS outstanding,
                 DATEDIFF(?, pi.created_at) AS age
          FROM purchase_invoices pi
          WHERE pi.created_at<=?
        ) x
        JOIN suppliers s ON s.id = x.supplier_id
        WHERE outstanding > 0
        GROUP BY s.id, s.name
        ORDER BY s.name";
        $st = $pdo->prepare($sql);
        $st->execute([$asof,$asof,$asof,$asof]);
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $totals = ['b0'=>0,'b31'=>0,'b61'=>0,'b90'=>0,'total'=>0];
        foreach ($rows as $r) {
            $totals['b0']   += (float)$r['bucket_0_30'];
            $totals['b31']  += (float)$r['bucket_31_60'];
            $totals['b61']  += (float)$r['bucket_61_90'];
            $totals['b90']  += (float)$r['bucket_90_plus'];
            $totals['total']+= (float)$r['total'];
        }

        $this->view('reports/ap_aging', [
            'asof'   => $asof,
            'rows'   => $rows,
            'totals' => $totals,
        ]);
    }

    /** AR Aging (new) */
    public function araging(): void {
        require_auth();
        $asof = $_GET['asof'] ?? date('Y-m-d');

        $pdo = DB::conn();
        $sql = "
        SELECT c.id AS customer_id, c.name AS customer_name,
               SUM(CASE WHEN age<=30  THEN outstanding ELSE 0 END) AS bucket_0_30,
               SUM(CASE WHEN age BETWEEN 31 AND 60 THEN outstanding ELSE 0 END) AS bucket_31_60,
               SUM(CASE WHEN age BETWEEN 61 AND 90 THEN outstanding ELSE 0 END) AS bucket_61_90,
               SUM(CASE WHEN age>90  THEN outstanding ELSE 0 END) AS bucket_90_plus,
               SUM(outstanding) AS total
        FROM (
          SELECT i.id, i.customer_id,
                 GREATEST(
                   i.total
                   - COALESCE((
                       SELECT SUM(ip.amount) FROM invoice_payments ip
                       WHERE ip.invoice_id=i.id AND ip.paid_at<=?
                     ),0)
                   - COALESCE((
                       SELECT SUM(sr.total) FROM sales_returns sr
                       WHERE sr.sales_invoice_id=i.id AND sr.created_at<=?
                     ),0)
                 ,0) AS outstanding,
                 DATEDIFF(?, i.created_at) AS age
          FROM invoices i
          WHERE i.created_at<=?
        ) x
        JOIN customers c ON c.id = x.customer_id
        WHERE outstanding > 0
        GROUP BY c.id, c.name
        ORDER BY c.name";
        $st = $pdo->prepare($sql);
        $st->execute([$asof,$asof,$asof,$asof]);
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $totals = ['b0'=>0,'b31'=>0,'b61'=>0,'b90'=>0,'total'=>0];
        foreach ($rows as $r) {
            $totals['b0']   += (float)$r['bucket_0_30'];
            $totals['b31']  += (float)$r['bucket_31_60'];
            $totals['b61']  += (float)$r['bucket_61_90'];
            $totals['b90']  += (float)$r['bucket_90_plus'];
            $totals['total']+= (float)$r['total'];
        }

        $this->view('reports/ar_aging', [
            'asof'   => $asof,
            'rows'   => $rows,
            'totals' => $totals,
        ]);
    }
}
