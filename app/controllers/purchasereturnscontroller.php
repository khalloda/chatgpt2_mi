<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\DB;
use App\Models\PurchaseReturn;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class PurchaseReturnsController extends Controller
{
    public function store(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/purchaseinvoices'); }

        $piId = (int)($_POST['invoice_id'] ?? 0);
        if ($piId <= 0) { flash_set('error','Missing invoice.'); redirect('/purchaseinvoices'); }

        $pdo = DB::conn();

        // invoice + supplier
        $st = $pdo->prepare("SELECT pi.*, s.name AS supplier_name
                             FROM purchase_invoices pi
                             JOIN suppliers s ON s.id = pi.supplier_id
                             WHERE pi.id=?");
        $st->execute([$piId]);
        $pi = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$pi) { flash_set('error','Invoice not found.'); redirect('/purchaseinvoices'); }

        // maps
        $received = PurchaseReturn::receivedMapByInvoice($piId);
        $returned = PurchaseReturn::returnedMapByInvoice($piId);

        // submission
        $pids   = $_POST['ret_product_id']   ?? [];
        $wids   = $_POST['ret_warehouse_id'] ?? [];
        $qtys   = $_POST['ret_qty']          ?? [];
        $prices = $_POST['ret_price']        ?? [];

        $lines = []; $subtotal = 0.0;
        for ($i=0, $n=max(count($pids),count($wids),count($qtys),count($prices)); $i<$n; $i++) {
            $pid   = (int)($pids[$i] ?? 0);
            $wid   = (int)($wids[$i] ?? 0);
            $qty   = (int)($qtys[$i] ?? 0);
            $price = (float)($prices[$i] ?? 0);
            if ($pid<=0 || $wid<=0 || $qty<=0) continue;

            $key = $pid.':'.$wid;
            $rec = (int)($received[$key] ?? 0);
            $ret = (int)($returned[$key] ?? 0);
            $remain = max(0, $rec - $ret);
            if ($remain <= 0) continue;
            if ($qty > $remain) $qty = $remain;

            $lineTotal = round($qty * $price, 2);
            $subtotal += $lineTotal;

            $lines[] = compact('pid','wid','qty','price','lineTotal');
            $returned[$key] = ($returned[$key] ?? 0) + $qty;
        }

        if (!$lines) { flash_set('error','Nothing to return.'); redirect('/purchaseinvoices/show?id='.$piId); }

        $taxRate   = (float)($pi['tax_rate'] ?? 0);
        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $total     = round($subtotal + $taxAmount, 2);

        $pdo->beginTransaction();
        try {
            $prNo = PurchaseReturn::nextNumber();
            $pdo->prepare("INSERT INTO purchase_returns
                (pr_no, purchase_invoice_id, supplier_id, subtotal, tax_rate, tax_amount, total)
                VALUES (?,?,?,?,?,?,?)")
                ->execute([$prNo, $piId, (int)$pi['supplier_id'], $subtotal, $taxRate, $taxAmount, $total]);
            $prId = (int)$pdo->lastInsertId();

            $ins = $pdo->prepare("INSERT INTO purchase_return_items
                (purchase_return_id, product_id, warehouse_id, qty, price, line_total)
                VALUES (?,?,?,?,?,?)");

            foreach ($lines as $ln) {
                $ins->execute([$prId, $ln['pid'], $ln['wid'], $ln['qty'], $ln['price'], $ln['lineTotal']]);

                // DECREMENT stock (product_stocks has no id)
                $upd = $pdo->prepare("UPDATE product_stocks
                                      SET qty_on_hand = GREATEST(qty_on_hand - ?, 0)
                                      WHERE product_id=? AND warehouse_id=?");
                $upd->execute([$ln['qty'], $ln['pid'], $ln['wid']]);
            }

            $pdo->commit();
            flash_set('success','Debit note '.$prNo.' created and stock reduced.');
            redirect('/purchaseinvoices/show?id='.$piId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error','Create purchase return failed: '.$e->getMessage());
            redirect('/purchaseinvoices/show?id='.$piId);
        }
    }

    public function printnote(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        if ($id<=0) { redirect('/purchaseinvoices'); }

        $pr = PurchaseReturn::findHead($id);
        if (!$pr) { flash_set('error','Debit note not found.'); redirect('/purchaseinvoices'); }

        $pdo = DB::conn();
        $st = $pdo->prepare("SELECT pi.*, s.name AS supplier_name
                             FROM purchase_invoices pi
                             JOIN suppliers s ON s.id = pi.supplier_id
                             WHERE pi.id=?");
        $st->execute([(int)$pr['purchase_invoice_id']]);
        $pi = $st->fetch(\PDO::FETCH_ASSOC) ?: [];

        $this->view_raw('purchasereturns/print', [
            'pr'    => $pr,
            'items' => PurchaseReturn::items((int)$pr['id']),
            'pi'    => $pi,
        ]);
    }
}
