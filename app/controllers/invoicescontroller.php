<?php declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Core\DB;
use App\Models\Note;

use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class InvoicesController extends Controller
{
    public function index(): void {
        require_auth();
        $this->view('invoices/index', ['items' => Invoice::all()]);
    }

    public function show(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $inv = Invoice::find($id);
        if (!$inv) { flash_set('error','Invoice not found.'); redirect('/invoices'); }
        $items = Invoice::items($id);
        $payments = Invoice::payments($id);
		$credits_total = \App\Models\SalesReturn::creditsTotalForInvoice($id);
		$returns = \App\Models\SalesReturn::returnsForInvoice($id);
		$ret_map = \App\Models\SalesReturn::returnedMapByInvoice($id);
        $this->view('invoices/view', [
            'i' => $inv,
            'items' => $items,
            'payments' => $payments,
			'credits_total' => $credits_total,
			'returns' => $returns,
			'ret_map' => $ret_map,
			'notes' => Note::for('sales_invoice', $id),
        ]);
    }

    // Create invoice from order
    public function createfromorder(): void {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/orders'); }
        $orderId = (int)($_POST['order_id'] ?? 0);

        $list = SalesOrder::all();
        $order = null; foreach ($list as $o) if ((int)$o['id'] === $orderId) { $order = $o; break; }
        if (!$order) { flash_set('error','Order not found.'); redirect('/orders'); }

        $pdo = DB::conn(); $pdo->beginTransaction();
        try {
            $invNo = Invoice::nextNumber();
            $ins = $pdo->prepare("INSERT INTO invoices (inv_no, sales_order_id, customer_id, tax_rate, subtotal, tax_amount, total, status)
                                  VALUES (?,?,?,?,?,?,?, 'unpaid')");
            $ins->execute([
                $invNo, $orderId, (int)$order['customer_id'],
                (float)$order['tax_rate'], (float)$order['subtotal'],
                (float)$order['tax_amount'], (float)$order['total']
            ]);
            $invId = (int)$pdo->lastInsertId();

            $oi = SalesOrder::items($orderId);
            $insItem = $pdo->prepare("INSERT INTO invoice_items (invoice_id, product_id, warehouse_id, qty, price, line_total)
                                      VALUES (?,?,?,?,?,?)");
            foreach ($oi as $it) {
                $insItem->execute([
                    $invId, (int)$it['product_id'], (int)$it['warehouse_id'],
                    (int)$it['qty'], (float)$it['price'], (float)$it['line_total']
                ]);
            }

            $pdo->commit();
            flash_set('success', 'Invoice '.$invNo.' created.');
            redirect('/invoices/show?id='.$invId);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash_set('error','Create invoice failed: '.$e->getMessage());
            redirect('/orders/show?id='.$orderId);
        }
    }

    // Print page
    public function printpage(): void {
        require_auth();
        $id = (int)($_GET['id'] ?? 0);
        $includeNotes = isset($_GET['include_notes']) && $_GET['include_notes'] === '1';
        $inv = Invoice::find($id);
        if (!$inv) { flash_set('error','Invoice not found.'); redirect('/invoices'); }
        $items = Invoice::items($id);
        $publicNotes = $includeNotes ? Note::publicFor('sales_invoice', $id) : [];
        $this->view_raw('invoices/print', [
            'i' => $inv,
            'items' => $items,
            'public_notes' => $publicNotes,
            'include_notes' => $includeNotes,
        ]);
    }
}
