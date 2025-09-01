<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use App\Models\PurchaseInvoice;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class ReceiptsController extends Controller
{
    /** Receive multiple lines for a Purchase Invoice (caps to remaining; increments stock; updates avg_cost; writes ledger) */
    public function store(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/purchaseinvoices'); }

        $piId = (int)($_POST['invoice_id'] ?? 0);
        $pi = PurchaseInvoice::find($piId);
        if (!$pi) { flash_set('error','Invoice not found.'); redirect('/purchaseinvoices'); }
        $poId = (int)$pi['purchase_order_id'];

        // Load ordered items (by PO) and current received
        $items = PurchaseInvoice::poItems($piId);
        $ordered = [];
        foreach ($items as $it) {
            $key = $it['product_id'].':'.$it['warehouse_id'];
            $ordered[$key] = (int)$it['qty'];
        }
        $received = PurchaseInvoice::receivedMapByPo($poId);

        // Read submitted quantities
        $pids   = $_POST['rec_product_id']   ?? [];
        $wids   = $_POST['rec_warehouse_id'] ?? [];
        $qtys   = $_POST['rec_qty']          ?? [];
        $prices = $_POST['rec_price']        ?? [];

        $pdo = DB::conn(); $pdo->beginTransaction();
        try {
            $insRec = $pdo->prepare("INSERT INTO receipts (purchase_invoice_id, product_id, warehouse_id, qty, price)
                                     VALUES (?,?,?,?,?)");

            $changed = false;

            for ($i=0, $n=max(count($pids),count($wids),count($qtys),count($prices)); $i<$n; $i++) {
                $pid   = (int)($pids[$i]   ?? 0);
                $wid   = (int)($wids[$i]   ?? 0);
                $qty   = (int)($qtys[$i]   ?? 0);
                $price = (float)($prices[$i] ?? 0);

                if ($pid<=0 || $wid<=0 || $qty<=0) continue;

                $key = $pid.':'.$wid;
                $ord = (int)($ordered[$key] ?? 0);
                $rec = (int)($received[$key] ?? 0);
                $remain = max(0, $ord - $rec);
                if ($remain <= 0) { continue; }

                if ($qty > $remain) { $qty = $remain; }

                // 1) Insert receipt line
                $insRec->execute([$piId, $pid, $wid, $qty, $price]);
                $receiptId = (int)$pdo->lastInsertId();
                $changed = true;

                // 2) Recompute weighted average cost & increment stock (composite key, no 'id' column)
                // lock row
                $st = $pdo->prepare("
                    SELECT qty_on_hand, avg_cost
                    FROM product_stocks
                    WHERE product_id=? AND warehouse_id=?
                    FOR UPDATE
                ");
                $st->execute([$pid, $wid]);
                $row = $st->fetch(\PDO::FETCH_ASSOC);

                if ($row) {
                    $oldQty  = (int)$row['qty_on_hand'];
                    $oldCost = (float)($row['avg_cost'] ?? 0.0);
                    $newQty  = $oldQty + $qty;
                    $newAvg  = $newQty > 0 ? ( ($oldQty * $oldCost) + ($qty * $price) ) / $newQty : 0.0;

                    $upd = $pdo->prepare("
                        UPDATE product_stocks
                           SET qty_on_hand = ?, avg_cost = ?
                         WHERE product_id=? AND warehouse_id=?
                    ");
                    $upd->execute([$newQty, $newAvg, $pid, $wid]);
                } else {
                    // first time: avg_cost = this purchase price
                    $ins = $pdo->prepare("
                        INSERT INTO product_stocks (product_id, warehouse_id, qty_on_hand, qty_reserved, avg_cost)
                        VALUES (?,?,?,?,?)
                    ");
                    $ins->execute([$pid, $wid, $qty, 0, $price]);
                }

                // 3) Valued inventory ledger (receipt)
                $pdo->prepare("
                    INSERT INTO inventory_ledger
                        (product_id, warehouse_id, doc_type, doc_id, qty_delta, unit_cost, value_delta)
                    VALUES (?,?,?,?,?,?,?)
                ")->execute([$pid, $wid, 'receipt', $receiptId, $qty, $price, $qty * $price]);

                // update local received map for this submission
                $received[$key] = ($received[$key] ?? 0) + $qty;
            }

            // If fully received, mark PO as received
            if ($changed) {
                $allOk = true;
                foreach ($ordered as $k => $ordQty) {
                    $recQty = (int)($received[$k] ?? 0);
                    if ($recQty < $ordQty) { $allOk = false; break; }
                }
                if ($allOk) {
                    $pdo->prepare("UPDATE purchase_orders SET status='received' WHERE id=?")->execute([$poId]);
                }
            }

            $pdo->commit();
            flash_set('success', $changed ? 'Receipt posted.' : 'Nothing to receive.');
            redirect('/purchaseinvoices/show?id='.$piId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error', 'Receive failed: '.$e->getMessage());
            redirect('/purchaseinvoices/show?id='.$piId);
        }
    }

    /** Optional: delete a receipt line and decrement stock (logs a valued adjustment in ledger; avg_cost unchanged) */
    public function destroy(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/purchaseinvoices'); }

        $id = (int)($_POST['id'] ?? 0);
        $piId = (int)($_POST['invoice_id'] ?? 0);

        if ($id <= 0 || $piId <= 0) { redirect('/purchaseinvoices'); }

        $pdo = DB::conn(); $pdo->beginTransaction();
        try {
            // fetch the receipt
            $st = $pdo->prepare("SELECT * FROM receipts WHERE id=? AND purchase_invoice_id=? FOR UPDATE");
            $st->execute([$id, $piId]);
            $r = $st->fetch(\PDO::FETCH_ASSOC);

            if ($r) {
                $pid = (int)$r['product_id'];
                $wid = (int)$r['warehouse_id'];
                $qty = (int)$r['qty'];

                // lock stock to read current avg_cost
                $st2 = $pdo->prepare("SELECT qty_on_hand, avg_cost FROM product_stocks WHERE product_id=? AND warehouse_id=? FOR UPDATE");
                $st2->execute([$pid,$wid]);
                $stock = $st2->fetch(\PDO::FETCH_ASSOC) ?: ['qty_on_hand'=>0,'avg_cost'=>0.0];
                $avg = (float)($stock['avg_cost'] ?? 0.0);

                // decrement stock by composite key
                $pdo->prepare("
                    UPDATE product_stocks
                       SET qty_on_hand = GREATEST(qty_on_hand - ?, 0)
                     WHERE product_id=? AND warehouse_id=?
                ")->execute([$qty, $pid, $wid]);

                // valued ledger: treat as adjustment (negative)
                $pdo->prepare("
                    INSERT INTO inventory_ledger
                        (product_id, warehouse_id, doc_type, doc_id, qty_delta, unit_cost, value_delta)
                    VALUES (?,?,?,?,?,?,?)
                ")->execute([$pid, $wid, 'adjustment', $id, -$qty, $avg, -$qty * $avg]);

                // delete receipt line
                $pdo->prepare("DELETE FROM receipts WHERE id=?")->execute([$id]);
            }

            $pdo->commit();
            flash_set('success','Receipt deleted.');
            redirect('/purchaseinvoices/show?id='.$piId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error','Delete failed: '.$e->getMessage());
            redirect('/purchaseinvoices/show?id='.$piId);
        }
    }

    public function printgrn(): void {
        require_auth();
        $piId = (int)($_GET['invoice_id'] ?? 0);
        $pi = \App\Models\PurchaseInvoice::find($piId);
        if (!$pi) { flash_set('error','Invoice not found.'); redirect('/purchaseinvoices'); }
        $items    = \App\Models\PurchaseInvoice::poItems($piId);
        $receipts = \App\Models\PurchaseInvoice::receipts($piId);
        $this->view_raw('receipts/print', [
            'pi'       => $pi,
            'items'    => $items,
            'receipts' => $receipts,
        ]);
    }
}
