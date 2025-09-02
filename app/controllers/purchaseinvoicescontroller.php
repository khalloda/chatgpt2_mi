<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Note;
use PDO;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class PurchaseInvoicesController extends Controller
{
    public function index(): void {
        require_auth();
        $this->view('purchaseinvoices/index', ['items' => PurchaseInvoice::all()]);
    }

    public function show(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $pi = \App\Models\PurchaseInvoice::find($id);
        if (!$pi) { flash_set('error','Invoice not found.'); redirect('/purchaseinvoices'); }

        $items       = \App\Models\PurchaseInvoice::poItems($id);
        $receivedMap = \App\Models\PurchaseInvoice::receivedMapByPo((int)$pi['purchase_order_id']);
        $receipts    = \App\Models\PurchaseInvoice::receipts($id);
        $payments    = \App\Models\SupplierPayment::forInvoice($id);
        $credits_total = \App\Models\PurchaseReturn::creditsTotalForInvoice($id);
        $pr_returns    = \App\Models\PurchaseReturn::returnsForInvoice($id);
        $rec_map       = \App\Models\PurchaseReturn::receivedMapByInvoice($id);
        $ret_map       = \App\Models\PurchaseReturn::returnedMapByInvoice($id);

        $this->view('purchaseinvoices/view', [
            'pi'            => $pi,
            'items'         => $items,
            'received'      => $receivedMap,
            'receipts'      => $receipts,
            'payments'      => $payments,
            'credits_total' => $credits_total,
            'pr_returns'    => $pr_returns,
            'rec_map'       => $rec_map,
            'ret_map'       => $ret_map,
            'notes'         => \App\Models\Note::for('purchase_invoice', $id),
        ]);
    }

    /**
     * Create a PI from a PO:
     * - PI number mirrors PO number:  POYYYY-#### -> PIYYYY-####
     * - If that number already exists, we try suffixes: -A, -B, ... to keep it readable.
     *   (This handles legacy PIs created earlier with a sequence.)
     */
    public function createfrompo(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/purchaseorders'); }

        $poId = (int)($_POST['purchase_order_id'] ?? 0);
        $po = PurchaseOrder::find($poId);
        if (!$po) { flash_set('error','PO not found.'); redirect('/purchaseorders'); }

        // If you want strict 1:1 (only one PI per PO), uncomment the next four lines:
        // $exists = DB::conn()->prepare("SELECT id FROM purchase_invoices WHERE purchase_order_id=? LIMIT 1");
        // $exists->execute([$poId]);
        // if ($row = $exists->fetch(PDO::FETCH_ASSOC)) { flash_set('success','Invoice already exists.'); redirect('/purchaseinvoices/show?id='.(int)$row['id']); return; }

        $pdo = DB::conn();
        $pdo->beginTransaction();
        try {
            // Build PI number based on PO number
            $piNo = $this->piNoFromPo((string)$po['po_no']);

            // Ensure uniqueness; if exists, try suffix -A, -B, ...
            $piNo = $this->uniquePiNo($piNo, $pdo);

            $st = $pdo->prepare("
                INSERT INTO purchase_invoices
                (pi_no, purchase_order_id, supplier_id, subtotal, tax_rate, tax_amount, total, created_at)
                VALUES (?,?,?,?,?,?,?, NOW())
            ");
            $st->execute([
                $piNo,
                $poId,
                (int)$po['supplier_id'],
                (float)$po['subtotal'],
                (float)$po['tax_rate'],
                (float)$po['tax_amount'],
                (float)$po['total']
            ]);

            $piId = (int)$pdo->lastInsertId();
            $pdo->commit();

            flash_set('success', 'Purchase invoice '.$piNo.' created.');
            redirect('/purchaseinvoices/show?id='.$piId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error','Create invoice failed: '.$e->getMessage());
            redirect('/purchaseorders/show?id='.$poId);
        }
    }

    /**
     * POST /purchaseinvoices/receive
     * Fields: id, line_id[], qty[]
     * - Uses purchase_receipts log to cap and compute totals (no received_qty column).
     */
    public function receive(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/purchaseinvoices'); }

        $piId = (int)($_POST['id'] ?? 0);
        $pi   = PurchaseInvoice::find($piId);
        if (!$pi) { flash_set('error','Invoice not found.'); redirect('/purchaseinvoices'); }

        $lineIds = $_POST['line_id'] ?? [];
        $qtys    = $_POST['qty'] ?? [];
        if (empty($lineIds)) { flash_set('error','Nothing to receive.'); redirect('/purchaseinvoices/show?id='.$piId); }

        $pdo = DB::conn();
        $pdo->beginTransaction();
        try {
            // Ensure product_stocks & purchase_receipts exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS product_stocks (
                  product_id INT UNSIGNED NOT NULL,
                  warehouse_id INT UNSIGNED NOT NULL,
                  qty_on_hand INT UNSIGNED NOT NULL DEFAULT 0,
                  qty_reserved INT UNSIGNED NOT NULL DEFAULT 0,
                  avg_cost DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                  PRIMARY KEY (product_id, warehouse_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS purchase_receipts (
                  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  purchase_invoice_id INT UNSIGNED NOT NULL,
                  purchase_order_item_id INT UNSIGNED NOT NULL,
                  warehouse_id INT UNSIGNED NOT NULL,
                  product_id INT UNSIGNED NOT NULL,
                  qty DECIMAL(12,4) NOT NULL,
                  unit_cost DECIMAL(12,4) NOT NULL,
                  created_at DATETIME NOT NULL,
                  KEY idx_pr_pi (purchase_invoice_id),
                  KEY idx_pr_poi (purchase_order_item_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $selItem = $pdo->prepare("
                SELECT poi.id, poi.product_id, poi.warehouse_id, poi.qty, poi.price
                FROM purchase_order_items poi
                JOIN purchase_orders po ON po.id = poi.purchase_order_id
                WHERE poi.id = ? AND po.id = ?
            ");
            $selReceived = $pdo->prepare("SELECT COALESCE(SUM(qty),0) FROM purchase_receipts WHERE purchase_order_item_id=?");

            $selPS = $pdo->prepare("SELECT qty_on_hand, avg_cost FROM product_stocks WHERE product_id=? AND warehouse_id=? FOR UPDATE");
            $updPS = $pdo->prepare("UPDATE product_stocks SET qty_on_hand=?, avg_cost=? WHERE product_id=? AND warehouse_id=?");
            $insPS = $pdo->prepare("INSERT INTO product_stocks (product_id, warehouse_id, qty_on_hand, avg_cost) VALUES (?,?,?,?)");

            $logRecv = $pdo->prepare("
                INSERT INTO purchase_receipts (purchase_invoice_id, purchase_order_item_id, warehouse_id, product_id, qty, unit_cost, created_at)
                VALUES (?,?,?,?,?,?, NOW())
            ");

            foreach ($lineIds as $i => $lid) {
                $lid = (int)$lid;
                $qty = (float)($qtys[$i] ?? 0);
                if ($lid <= 0 || $qty <= 0) { continue; }

                // Load the PO line
                $selItem->execute([$lid, (int)$pi['purchase_order_id']]);
                $line = $selItem->fetch(PDO::FETCH_ASSOC);
                if (!$line) { throw new \RuntimeException('Line not found: '.$lid); }

                $ordered = (float)$line['qty'];

                // How much already received for this line?
                $selReceived->execute([$lid]);
                $already = (float)$selReceived->fetchColumn();

                if ($already + $qty > $ordered) {
                    throw new \RuntimeException('Receive qty exceeds ordered for line '.$lid);
                }

                $product_id   = (int)$line['product_id'];
                $warehouse_id = (int)$line['warehouse_id'];
                $unit_cost    = (float)$line['price'];

                // Lock stock row
                $selPS->execute([$product_id, $warehouse_id]);
                $stock = $selPS->fetch(PDO::FETCH_ASSOC);

                $old_qty = $stock ? (float)$stock['qty_on_hand'] : 0.0;
                $old_avg = $stock ? (float)$stock['avg_cost']    : 0.0;

                $new_qty = $old_qty + $qty;
                $new_avg = $new_qty > 0 ? (($old_qty * $old_avg) + ($qty * $unit_cost)) / $new_qty : $unit_cost;

                if ($stock) {
                    $updPS->execute([$new_qty, $new_avg, $product_id, $warehouse_id]);
                } else {
                    $insPS->execute([$product_id, $warehouse_id, (int)$qty, $unit_cost]);
                }

                // Log receipt
                $logRecv->execute([$piId, $lid, $warehouse_id, $product_id, $qty, $unit_cost]);
            }

            // PO status based on receipts vs ordered
            $poId = (int)$pi['purchase_order_id'];
            $tot  = $pdo->query("
                SELECT
                  COALESCE(SUM(poi.qty),0) AS q,
                  COALESCE(SUM(r.qty),0)   AS r
                FROM purchase_order_items poi
                LEFT JOIN purchase_receipts r ON r.purchase_order_item_id = poi.id
                WHERE poi.purchase_order_id = ".(int)$poId."
            ")->fetch(PDO::FETCH_ASSOC) ?: ['q'=>0,'r'=>0];

            $status = 'ordered';
            if ((float)$tot['r'] > 0 && (float)$tot['r'] < (float)$tot['q']) $status = 'partially_received';
            if ((float)$tot['r'] >= (float)$tot['q'] && (float)$tot['q'] > 0) $status = 'received';

            $pdo->prepare("UPDATE purchase_orders SET status=? WHERE id=?")->execute([$status, $poId]);

            $pdo->commit();
            flash_set('success','Goods received and stock updated.');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error','Receive failed: '.$e->getMessage());
        }

        redirect('/purchaseinvoices/show?id='.$piId);
    }

    public function printpage(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $includeNotes = isset($_GET['include_notes']) && $_GET['include_notes'] === '1';
        $pi = PurchaseInvoice::find($id);
        if (!$pi) { flash_set('error','Invoice not found.'); redirect('/purchaseinvoices'); }

        $items = PurchaseInvoice::poItems($id);
        $publicNotes = $includeNotes ? Note::publicFor('purchase_invoice', $id) : [];
        $this->view_raw('purchaseinvoices/print', [
            'pi' => $pi,
            'items' => $items,
            'public_notes' => $publicNotes,
            'include_notes' => $includeNotes,
        ]);
    }

    // --------------------------
    // Helpers
    // --------------------------

    /**
     * Convert PO number to PI number.
     * Accepts common formats like "PO2025-0019" (case-insensitive).
     * Falls back to prefix swap if pattern changes.
     */
    private function piNoFromPo(string $poNo): string
    {
        $poNo = trim($poNo);
        // PO2025-0019  -> PI2025-0019
        if (preg_match('/^PO(\d{4})-(\d{4})$/i', $poNo, $m)) {
            return sprintf('PI%s-%s', $m[1], $m[2]);
        }
        // Fallback: just swap leading PO -> PI if present
        if (stripos($poNo, 'PO') === 0) {
            return 'PI' . substr($poNo, 2);
        }
        // Last resort: prefix the whole string
        return 'PI' . $poNo;
    }

    /**
     * Ensures a unique pi_no. If base exists, tries -A, -B, ... -Z, then -2, -3...
     */
    private function uniquePiNo(string $base, PDO $pdo): string
    {
        $check = $pdo->prepare("SELECT 1 FROM purchase_invoices WHERE pi_no = ? LIMIT 1");

        // 1) try the base
        $check->execute([$base]);
        if (!$check->fetchColumn()) return $base;

        // 2) try -A..-Z
        foreach (range('A', 'Z') as $ch) {
            $try = $base . '-' . $ch;
            $check->execute([$try]);
            if (!$check->fetchColumn()) return $try;
        }

        // 3) try -2..-99
        for ($i=2; $i<100; $i++) {
            $try = $base . '-' . $i;
            $check->execute([$try]);
            if (!$check->fetchColumn()) return $try;
        }

        // 4) very unlikely: append timestamp fragment
        return $base . '-' . date('His');
    }
}
