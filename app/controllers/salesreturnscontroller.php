<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use App\Models\SalesReturn;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class SalesReturnsController extends Controller
{
    /** Create a credit note (sales return) from a sales invoice lines submission */
    public function store(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/invoices'); }

        $invId = (int)($_POST['invoice_id'] ?? 0);
        if ($invId <= 0) { flash_set('error','Missing invoice.'); redirect('/invoices'); }

        // Load invoice head
        $pdo = DB::conn();
        $st = $pdo->prepare("SELECT si.*, c.name AS client_name
                             FROM sales_invoices si
                             JOIN clients c ON c.id = si.client_id
                             WHERE si.id=?");
        $st->execute([$invId]);
        $inv = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$inv) { flash_set('error','Invoice not found.'); redirect('/invoices'); }

        // Load invoiced items and previous returns map
        $items      = SalesReturn::invoiceItems($invId);
        $returned   = SalesReturn::returnedMapByInvoice($invId);
        $orderedMap = [];
        foreach ($items as $it) { $orderedMap[$it['product_id'].':'.$it['warehouse_id']] = (int)$it['qty']; }

        // Read submission arrays
        $pids   = $_POST['ret_product_id']   ?? [];
        $wids   = $_POST['ret_warehouse_id'] ?? [];
        $qtys   = $_POST['ret_qty']          ?? [];
        $prices = $_POST['ret_price']        ?? [];

        // Build valid return lines (cap to remaining)
        $lines = [];
        $subtotal = 0.0;

        for ($i=0, $n=max(count($pids),count($wids),count($qtys),count($prices)); $i<$n; $i++) {
            $pid   = (int)($pids[$i]   ?? 0);
            $wid   = (int)($wids[$i]   ?? 0);
            $qty   = (int)($qtys[$i]   ?? 0);
            $price = (float)($prices[$i] ?? 0);

            if ($pid<=0 || $wid<=0 || $qty<=0) continue;

            $key   = $pid.':'.$wid;
            $sold  = (int)($orderedMap[$key] ?? 0);
            $prev  = (int)($returned[$key]    ?? 0);
            $remain = max(0, $sold - $prev);
            if ($remain <= 0) { continue; }
            if ($qty > $remain) { $qty = $remain; }

            $lineTotal = round($qty * $price, 2);
            $subtotal += $lineTotal;

            $lines[] = [
                'product_id' => $pid,
                'warehouse_id' => $wid,
                'qty' => $qty,
                'price' => $price,
                'line_total' => $lineTotal,
            ];

            // update local returned map so same submission respects caps
            $returned[$key] = ($returned[$key] ?? 0) + $qty;
        }

        if (!$lines) {
            flash_set('error','Nothing to return.');
            redirect('/invoices/show?id='.$invId);
        }

        // Totals (reuse invoice tax_rate)
        $taxRate   = (float)($inv['tax_rate'] ?? 0);
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $total     = round($subtotal + $taxAmount, 2);

        // Persist head + lines and restock
        $pdo->beginTransaction();
        try {
            $srNo = SalesReturn::nextNumber();
            $insH = $pdo->prepare("INSERT INTO sales_returns
                (sr_no, sales_invoice_id, client_id, subtotal, tax_rate, tax_amount, total)
                VALUES (?,?,?,?,?,?,?)");
            $insH->execute([$srNo, $invId, (int)$inv['client_id'], $subtotal, $taxRate, $taxAmount, $total]);
            $srId = (int)$pdo->lastInsertId();

            $insL = $pdo->prepare("INSERT INTO sales_return_items
                (sales_return_id, product_id, warehouse_id, qty, price, line_total)
                VALUES (?,?,?,?,?,?)");

            foreach ($lines as $ln) {
                $insL->execute([$srId, $ln['product_id'], $ln['warehouse_id'], $ln['qty'], $ln['price'], $ln['line_total']]);

                // RESTOCK returned items (product_stocks has NO id column)
                $sel = $pdo->prepare("SELECT qty_on_hand FROM product_stocks WHERE product_id=? AND warehouse_id=? LIMIT 1");
                $sel->execute([$ln['product_id'], $ln['warehouse_id']]);
                $row = $sel->fetch(\PDO::FETCH_ASSOC);
                if ($row) {
                    $upd = $pdo->prepare("UPDATE product_stocks
                                          SET qty_on_hand = qty_on_hand + ?
                                          WHERE product_id=? AND warehouse_id=?");
                    $upd->execute([$ln['qty'], $ln['product_id'], $ln['warehouse_id']]);
                } else {
                    $ins = $pdo->prepare("INSERT INTO product_stocks (product_id, warehouse_id, qty_on_hand, qty_reserved)
                                          VALUES (?,?,?,0)");
                    $ins->execute([$ln['product_id'], $ln['warehouse_id'], $ln['qty']]);
                }
            }

            $pdo->commit();
            flash_set('success', 'Credit note '.$srNo.' created and stock updated.');
            redirect('/invoices/show?id='.$invId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error', 'Create return failed: '.$e->getMessage());
            redirect('/invoices/show?id='.$invId);
        }
    }

    /** Print the credit note */
    public function printnote(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { redirect('/invoices'); }

        $sr = SalesReturn::findHead($id);
        if (!$sr) { flash_set('error','Credit note not found.'); redirect('/invoices'); }

        // invoice head for context
        $pdo = DB::conn();
        $st = $pdo->prepare("SELECT si.*, c.name AS client_name
                             FROM sales_invoices si
                             JOIN clients c ON c.id = si.client_id
                             WHERE si.id=?");
        $st->execute([(int)$sr['sales_invoice_id']]);
        $inv = $st->fetch(\PDO::FETCH_ASSOC) ?: [];

        $this->view_raw('salesreturns/print', [
            'sr'    => $sr,
            'items' => SalesReturn::items((int)$sr['id']),
            'inv'   => $inv,
        ]);
    }
}
